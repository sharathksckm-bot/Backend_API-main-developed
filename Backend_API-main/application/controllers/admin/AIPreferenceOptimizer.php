<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

/**
 * AI Preference Optimizer Controller
 * 
 * Uses OpenAI GPT-5.2 to generate optimized college preference lists
 * based on historical cutoff data and user preferences
 * 
 * @category   Controllers
 * @package    Admin
 * @subpackage AIPreferenceOptimizer
 * @version    1.0
 */

if (!defined("BASEPATH")) {
    exit("No direct script access allowed");
}

class AIPreferenceOptimizer extends CI_Controller
{
    private $openai_api_key;
    private $openai_endpoint = 'https://emergentai.openai.azure.com/openai/deployments/gpt-5-2/chat/completions?api-version=2024-02-01';

    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'));
        if ($this->input->server('REQUEST_METHOD') == 'OPTIONS') {
            $data['status'] = 'ok';
            echo json_encode($data);
            exit;
        }
        $this->load->model("admin/NeetPredictor_model", "", true);
        $this->load->model("admin/AIPreferenceOptimizer_model", "", true);
        $this->load->library("Utility");
        
        // Get OpenAI API key from config or environment
        $this->openai_api_key = getenv('EMERGENT_LLM_KEY') ?: $this->config->item('openai_api_key');
    }

    /**
     * Generate AI-optimized preference list
     * Main endpoint for mobile app
     */
    public function generateOptimizedPreferences()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        // Validate required inputs
        if (!isset($data->rank) || !isset($data->category)) {
            $response["response_code"] = "400";
            $response["response_message"] = "Missing required fields: rank, category";
            echo json_encode($response);
            exit();
        }

        $rank = intval($data->rank);
        $category = $data->category;
        $domicile_state = isset($data->domicile_state) ? $data->domicile_state : '';
        $preferred_state = isset($data->preferred_state) ? $data->preferred_state : '';
        $course = isset($data->course) ? $data->course : 'MBBS';
        $counseling_type = isset($data->counseling_type) ? $data->counseling_type : '';
        $max_fee = isset($data->max_fee) ? intval($data->max_fee) : 0;
        $location_preferences = isset($data->location_preferences) ? $data->location_preferences : [];
        $priority = isset($data->priority) ? $data->priority : 'balanced'; // 'probability', 'ranking', 'fee', 'balanced'

        // Get latest year cutoff data
        $latestYear = $this->NeetPredictor_model->getLatestYear();

        // Fetch cutoff data for analysis
        $cutoffData = $this->NeetPredictor_model->getCutoffDataForPrediction(
            $latestYear, $preferred_state, $course, $category, $counseling_type
        );

        if (empty($cutoffData)) {
            $response["response_code"] = "404";
            $response["response_message"] = "No cutoff data found for the given criteria";
            echo json_encode($response);
            exit();
        }

        // Classify colleges into Safe/Possible/Dream
        $safeColleges = [];
        $possibleColleges = [];
        $dreamColleges = [];

        foreach ($cutoffData as $college) {
            $closingRank = intval($college->closing_rank);
            if ($closingRank <= 0) continue;
            if ($max_fee > 0 && intval($college->annual_fee) > $max_fee) continue;

            $collegeData = [
                'college_name' => $college->college_name,
                'state' => $college->state,
                'college_type' => $college->college_type,
                'closing_rank' => $closingRank,
                'opening_rank' => intval($college->opening_rank),
                'annual_fee' => intval($college->annual_fee),
                'course' => $college->course,
                'round' => $college->round
            ];

            if ($rank <= $closingRank * 0.85) {
                $collegeData['probability'] = 'High';
                $collegeData['chance_type'] = 'Safe';
                $collegeData['admission_score'] = 90 + (($closingRank - $rank) / $closingRank * 10);
                $safeColleges[] = $collegeData;
            } elseif ($rank <= $closingRank) {
                $collegeData['probability'] = 'Medium';
                $collegeData['chance_type'] = 'Possible';
                $collegeData['admission_score'] = 50 + (($closingRank - $rank) / $closingRank * 40);
                $possibleColleges[] = $collegeData;
            } elseif ($rank <= $closingRank * 1.15) {
                $collegeData['probability'] = 'Low';
                $collegeData['chance_type'] = 'Dream';
                $collegeData['admission_score'] = 10 + max(0, (($closingRank * 1.15 - $rank) / ($closingRank * 0.15) * 40));
                $dreamColleges[] = $collegeData;
            }
        }

        // Prepare data for AI analysis
        $allColleges = array_merge($safeColleges, $possibleColleges, $dreamColleges);
        
        if (empty($allColleges)) {
            $response["response_code"] = "404";
            $response["response_message"] = "No colleges found matching your criteria";
            echo json_encode($response);
            exit();
        }

        // Generate AI-optimized preferences
        $aiResponse = $this->callOpenAI($rank, $category, $allColleges, $priority, $max_fee, $location_preferences, $preferred_state);

        if ($aiResponse['success']) {
            // Log the optimization request
            $this->AIPreferenceOptimizer_model->logOptimizationRequest([
                'user_id' => isset($data->user_id) ? $data->user_id : null,
                'rank' => $rank,
                'category' => $category,
                'state' => $preferred_state,
                'priority' => $priority,
                'colleges_analyzed' => count($allColleges),
                'response_colleges' => count($aiResponse['preferences'])
            ]);

            $response["response_code"] = "200";
            $response["response_message"] = "Success";
            $response["cutoff_year"] = $latestYear;
            $response["rank"] = $rank;
            $response["category"] = $category;
            $response["optimization_priority"] = $priority;
            $response["ai_analysis"] = $aiResponse['analysis'];
            $response["optimized_preferences"] = $aiResponse['preferences'];
            $response["total_colleges_analyzed"] = count($allColleges);
            $response["safe_count"] = count($safeColleges);
            $response["possible_count"] = count($possibleColleges);
            $response["dream_count"] = count($dreamColleges);
        } else {
            // Fallback to algorithmic optimization if AI fails
            $response["response_code"] = "200";
            $response["response_message"] = "Success (Algorithmic)";
            $response["cutoff_year"] = $latestYear;
            $response["optimization_priority"] = $priority;
            $response["ai_analysis"] = "AI optimization unavailable. Using algorithmic sorting.";
            $response["optimized_preferences"] = $this->algorithmicOptimization($allColleges, $priority);
            $response["total_colleges_analyzed"] = count($allColleges);
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Call OpenAI GPT-5.2 API for preference optimization
     */
    private function callOpenAI($rank, $category, $colleges, $priority, $maxFee, $locationPrefs, $preferredState)
    {
        if (empty($this->openai_api_key)) {
            return ['success' => false, 'error' => 'API key not configured'];
        }

        // Prepare college data summary for AI
        $collegeSummary = [];
        foreach (array_slice($colleges, 0, 50) as $index => $college) { // Limit to 50 colleges for API
            $collegeSummary[] = [
                'id' => $index + 1,
                'name' => $college['college_name'],
                'state' => $college['state'],
                'type' => $college['college_type'],
                'closing_rank' => $college['closing_rank'],
                'fee' => $college['annual_fee'],
                'chance' => $college['chance_type'],
                'score' => round($college['admission_score'], 1)
            ];
        }

        $systemMessage = "You are an expert NEET counseling advisor in India. Your task is to optimize college preference lists for medical admissions based on historical cutoff data and student preferences.

You should consider:
1. Admission probability (higher is better)
2. College reputation and type (Government > Private)
3. Fee constraints (within budget is preferred)
4. Location preferences
5. A balanced mix of Safe, Possible, and Dream colleges

Return your response in valid JSON format with:
- 'analysis': A brief analysis of the student's situation (2-3 sentences)
- 'strategy': Recommended filling strategy
- 'preferences': Array of college IDs in recommended order with 'id', 'reason' for each";

        $userMessage = "Student Details:
- NEET Rank: $rank
- Category: $category
- Preferred State: $preferredState
- Max Fee Budget: " . ($maxFee > 0 ? "₹" . number_format($maxFee) : "No limit") . "
- Optimization Priority: $priority

Available Colleges (sorted by admission score):
" . json_encode($collegeSummary, JSON_PRETTY_PRINT) . "

Please provide an optimized preference list of top 20-25 colleges with reasoning.";

        $postData = [
            'model' => 'gpt-5.2',
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openai_api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'API request failed'];
        }

        $responseData = json_decode($result, true);
        
        if (!isset($responseData['choices'][0]['message']['content'])) {
            return ['success' => false, 'error' => 'Invalid API response'];
        }

        $aiContent = $responseData['choices'][0]['message']['content'];
        
        // Parse AI response
        $parsedResponse = $this->parseAIResponse($aiContent, $colleges);
        
        return [
            'success' => true,
            'analysis' => $parsedResponse['analysis'],
            'preferences' => $parsedResponse['preferences']
        ];
    }

    /**
     * Parse AI response and map back to college data
     */
    private function parseAIResponse($aiContent, $colleges)
    {
        // Try to parse JSON from AI response
        $jsonStart = strpos($aiContent, '{');
        $jsonEnd = strrpos($aiContent, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($aiContent, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonStr, true);
            
            if ($parsed && isset($parsed['preferences'])) {
                $optimizedList = [];
                $preference = 1;
                
                foreach ($parsed['preferences'] as $pref) {
                    $collegeId = isset($pref['id']) ? intval($pref['id']) - 1 : -1;
                    
                    if ($collegeId >= 0 && $collegeId < count($colleges)) {
                        $college = $colleges[$collegeId];
                        $college['preference_number'] = $preference++;
                        $college['ai_reason'] = isset($pref['reason']) ? $pref['reason'] : '';
                        $optimizedList[] = $college;
                    }
                }
                
                return [
                    'analysis' => isset($parsed['analysis']) ? $parsed['analysis'] : 'AI-optimized preference list generated.',
                    'strategy' => isset($parsed['strategy']) ? $parsed['strategy'] : '',
                    'preferences' => $optimizedList
                ];
            }
        }
        
        // Fallback: use algorithmic optimization
        return [
            'analysis' => 'Preference list optimized using balanced algorithm.',
            'preferences' => $this->algorithmicOptimization($colleges, 'balanced')
        ];
    }

    /**
     * Algorithmic optimization fallback
     */
    private function algorithmicOptimization($colleges, $priority)
    {
        // Sort based on priority
        usort($colleges, function($a, $b) use ($priority) {
            switch ($priority) {
                case 'probability':
                    // Sort by admission score (highest first)
                    return $b['admission_score'] - $a['admission_score'];
                    
                case 'ranking':
                    // Government first, then by closing rank
                    $typeOrder = ['Government' => 0, 'Private' => 1, 'Deemed' => 2];
                    $typeA = isset($typeOrder[$a['college_type']]) ? $typeOrder[$a['college_type']] : 3;
                    $typeB = isset($typeOrder[$b['college_type']]) ? $typeOrder[$b['college_type']] : 3;
                    if ($typeA !== $typeB) return $typeA - $typeB;
                    return $a['closing_rank'] - $b['closing_rank'];
                    
                case 'fee':
                    // Lower fee first
                    return $a['annual_fee'] - $b['annual_fee'];
                    
                case 'balanced':
                default:
                    // Balanced: weighted score
                    $scoreA = $a['admission_score'] * 0.5;
                    $scoreA += ($a['college_type'] === 'Government' ? 20 : 0);
                    $scoreA += ($a['annual_fee'] < 100000 ? 10 : 0);
                    
                    $scoreB = $b['admission_score'] * 0.5;
                    $scoreB += ($b['college_type'] === 'Government' ? 20 : 0);
                    $scoreB += ($b['annual_fee'] < 100000 ? 10 : 0);
                    
                    return $scoreB - $scoreA;
            }
        });

        // Add preference numbers
        $result = [];
        $preference = 1;
        foreach (array_slice($colleges, 0, 25) as $college) {
            $college['preference_number'] = $preference++;
            $college['ai_reason'] = $this->generateReason($college, $priority);
            $result[] = $college;
        }

        return $result;
    }

    /**
     * Generate reason for preference
     */
    private function generateReason($college, $priority)
    {
        $reasons = [];
        
        if ($college['chance_type'] === 'Safe') {
            $reasons[] = 'High admission probability';
        } elseif ($college['chance_type'] === 'Possible') {
            $reasons[] = 'Moderate admission chance';
        } else {
            $reasons[] = 'Worth trying in later rounds';
        }
        
        if ($college['college_type'] === 'Government') {
            $reasons[] = 'Government college (lower fees)';
        }
        
        if ($college['annual_fee'] < 100000) {
            $reasons[] = 'Affordable fee structure';
        }
        
        return implode('. ', $reasons);
    }

    /**
     * Get optimization history for user
     */
    public function getOptimizationHistory()
    {
        $data = json_decode(file_get_contents("php://input"));
        
        if ($this->input->server("REQUEST_METHOD") == "OPTIONS") {
            $data["status"] = "ok";
            echo json_encode($data);
            exit();
        }

        $user_id = isset($data->user_id) ? $data->user_id : null;
        $limit = isset($data->limit) ? $data->limit : 10;

        if (!$user_id) {
            $response["response_code"] = "400";
            $response["response_message"] = "User ID required";
            echo json_encode($response);
            exit();
        }

        $history = $this->AIPreferenceOptimizer_model->getOptimizationHistory($user_id, $limit);

        $response["response_code"] = "200";
        $response["response_message"] = "Success";
        $response["response_data"] = $history;

        echo json_encode($response);
        exit();
    }
}
