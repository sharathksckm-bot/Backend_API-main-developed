<?php

class Review_model extends CI_Model
{
    private $table = 'Review';

    public function __construct()
    {

        parent::__construct(); {
            $this->load->database();
        }
    }

    public function getReviewDetails($collegeId = '', $page = 1, $rate = '')
    {
        $this->db->where('college_id', $collegeId);
        $this->db->where('status', 1);
        if (!empty($rate) && (int)$rate > 0 && (int)$rate <= 5) {
            $this->db->where('placement_rate <=', $rate)
                ->where('infrastructure_rate <=', $rate)
                ->where('faculty_rate <=', $rate)
                ->where('hostel_rate <=', $rate)
                ->where('campus_rate <=', $rate)
                ->where('money_rate <=', $rate);
        }
        $data = $this->db->get('review')->result_array();
        $totalRating = $totalPlacementRate = $totalInfrastructureRate = $totalFacultyRate = $totalHostelRate = $totalCampusRate = $totalMoneyRate = 0;
        $one2two = $two2three = $three2four = $four2five = 0;
        if (!empty($data)) {
            foreach ($data as $review) {
                $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
                $totalRating += ($avg * 100) / 5;


                $totalPlacementRate += ($review['placement_rate'] * 100) / 5;
                $totalInfrastructureRate += ($review['infrastructure_rate'] * 100) / 5;
                $totalFacultyRate += ($review['faculty_rate'] * 100) / 5;
                $totalHostelRate += ($review['hostel_rate'] * 100) / 5;
                $totalCampusRate += ($review['campus_rate'] * 100) / 5;
                $totalMoneyRate += ($review['money_rate'] * 100) / 5;

                if ($avg <= 2) {
                    $one2two++;
                } elseif ($avg <= 3) {
                    $two2three++;
                } elseif ($avg <= 4) {
                    $three2four++;
                } else {
                    $four2five++;
                }
            }
        }
        $totalRating = ($totalRating > 0) ? number_format($totalRating / count($data), 1) : 0;
        $totalPlacementRate = ($totalPlacementRate > 0) ? number_format($totalPlacementRate / count($data), 1) : 0;
        $totalInfrastructureRate = ($totalInfrastructureRate > 0) ? number_format($totalInfrastructureRate / count($data), 1) : 0;
        $totalFacultyRate = ($totalFacultyRate > 0) ? number_format($totalFacultyRate / count($data), 1) : 0;
        $totalHostelRate = ($totalHostelRate > 0) ? number_format($totalHostelRate / count($data), 1) : 0;
        $totalCampusRate = ($totalCampusRate > 0) ? number_format($totalCampusRate / count($data), 1) : 0;
        $totalMoneyRate = ($totalMoneyRate > 0) ? number_format($totalMoneyRate / count($data), 1) : 0;
        $offset = ($page - 1) * 10;

        return [
            'totalRate' => $totalRating,
            'totalRateCount' => ($totalRating > 0) ? number_format((($totalRating * 5) / 100), 1) : 0,
            'totalReview' => count($data),
            'totalPlacementRate' => $totalPlacementRate,
            'totalPlacementRateCount' => ($totalPlacementRate > 0) ? number_format((($totalPlacementRate * 5) / 100), 1) : 0,
            'totalInfrastructureRate' => $totalInfrastructureRate,
            'totalInfrastructureRateCount' => ($totalInfrastructureRate > 0) ? number_format((($totalInfrastructureRate * 5) / 100), 1) : 0,
            'totalFacultyRate' => $totalFacultyRate,
            'totalFacultyRateCount' => ($totalFacultyRate > 0) ? number_format((($totalFacultyRate * 5) / 100), 1) : 0,
            'totalHostelRate' => $totalHostelRate,
            'totalHostelRateCount' => ($totalHostelRate > 0) ? number_format((($totalHostelRate * 5) / 100), 1) : 0,
            'totalHostelRate' => $totalHostelRate,
            'totalHostelRateCount' => ($totalHostelRate > 0) ? number_format((($totalHostelRate * 5) / 100), 1) : 0,
            'totalCampusRate' => $totalCampusRate,
            'totalCampusRateCount' => ($totalCampusRate > 0) ? number_format((($totalCampusRate * 5) / 100), 1) : 0,
            'totalMoneyRate' => $totalMoneyRate,
            'totalMoneyRateCount' => ($totalMoneyRate > 0) ? number_format((($totalMoneyRate * 5) / 100), 1) : 0,
            'one2twoRate' => ($one2two > 0) ? intval(($one2two / count($data) * 100)) : 0,
            'two2threeRate' => ($two2three > 0) ? intval(($two2three / count($data) * 100)) : 0,
            'three2fourRate' => ($three2four > 0) ? intval(($three2four / count($data) * 100)) : 0,
            'four2fiveRate' => ($four2five > 0) ? intval(($four2five / count($data) * 100)) : 0,
            'reviews' => $this->getCollegeReviews($collegeId, 10, $offset, '', 'DESC', '', '', $rate),

        ];
    }

    function getCollegeReviews($collegeId, $limit = 0, $start = 0, $orderByColumn = '', $orderBy = 'DESC', $courseId = '', $courseType = '', $rate = '')
    {
        $this->db->select("r.*,  CONCAT(u.f_name,' ',u.l_name) as user_name");
        $this->db->limit($limit, $start);
        if (empty($orderByColumn)) {
            $this->db->order_by('placement_rate ' . $orderBy . ', infrastructure_rate ' . $orderBy . ', faculty_rate ' . $orderBy . ',hostel_rate ' . $orderBy . ',campus_rate ' . $orderBy . ',money_rate ' . $orderBy);
        } else {
            $this->db->order_by($orderByColumn, $orderBy);
        }

        $this->db->join('users u', 'u.id = r.user_id', 'left');
        $this->db->where('r.college_id', $collegeId);
        $this->db->where('r.status', 1);
        if (!empty($courseId) && !empty($courseType)) {
            $this->db->where('r.course_id', $courseId);
            $this->db->where('r.course_type', $courseType);
        }

        if (!empty($rate) && (int)$rate > 0 && (int)$rate <= 5) {
            $this->db->where('r.placement_rate <=', $rate)
                ->where('r.infrastructure_rate <=', $rate)
                ->where('r.faculty_rate <=', $rate)
                ->where('r.hostel_rate <=', $rate)
                ->where('r.campus_rate <=', $rate)
                ->where('r.money_rate <=', $rate);
        }

        $data =  $this->db->get('review r')->result_array();
        if (!empty($data)) {
            foreach ($data as &$review) {
                $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
                $review['totalRating'] = number_format(($avg * 100) / 5, 1);
                $review['totalRatingCount'] = number_format($avg, 1);
                $review['created'] = date('d-M-Y', strtotime($review['created']));
            }
        }
        return $data;
    }

    function getCollegeTotalRate($collegeId = '')
    {
        $reviewDt = $this->getReviewBreakup($collegeId);
        $totalRate = $totalRateCount = $totalReview = 0;
        if (!empty($reviewDt[$collegeId])) {
            $data = $reviewDt[$collegeId];
            $totalRate = $data["ratingPercent"];
            $totalRateCount = $data["totalRating"];
            $totalReview = $data["total_reviews"];
        }
        return [
            'totalRate' => $totalRate,
            'totalRateCount' => $totalRateCount,
            'totalReview' => $totalReview
        ];
    }


    function getReviewBreakup($collegeIds = [])
    {
        $returnData = [];
        $collegeIds = !is_array($collegeIds) ? [$collegeIds] : $collegeIds;
        if (!empty($collegeIds)) {
            foreach ($collegeIds as $collegeId) {
                $avgReview = $this->db->select('AVG(placement_rate) as placement_rate, AVG(infrastructure_rate) as infrastructure_rate, AVG(faculty_rate) as faculty_rate, AVG(hostel_rate) as hostel_rate, AVG(campus_rate) as campus_rate, AVG(money_rate) as money_rate, COUNT(*) as total_reviews')
                    ->where('college_id', $collegeId)
                    ->where('status', '1')
                    ->get('review r')->row();
                if (!empty($avgReview)) {
                    $returnData[$collegeId]['total_reviews'] = $avgReview->total_reviews;
                    unset($avgReview->total_reviews);
                    $avgReview = (array)$avgReview;
                    foreach ($avgReview as $key => &$value) {
                        if (is_numeric($value)) {
                            $value = number_format($value, 1);
                        } else {
                            // Handle the case where $value is not a valid number
                            // For example, you might set it to 0 or a default value
                            $value = 0;
                        }
                    }

                    $returnData[$collegeId]['catAvgReview'] = (array)$avgReview;

                    $reviews = $this->db->where('college_id', $collegeId)->where('status', '1')->get('review r')->result_array();
                    $returnData[$collegeId]['totalRating'] = 0;
                    $returnData[$collegeId]['ratingPercent'] = 0;
                    if (!empty($reviews)) {
                        $totalAvg = 0;
                        foreach ($reviews as $review) {
                            $avg = ($review['placement_rate'] + $review['infrastructure_rate'] + $review['faculty_rate'] + $review['hostel_rate'] + $review['campus_rate'] + $review['money_rate']) / 6;
                            $totalAvg += $avg;
                        }
                        $returnData[$collegeId]['totalRating'] = number_format($totalAvg / count($reviews), 1);
                        $returnData[$collegeId]['ratingPercent'] = ($returnData[$collegeId]['totalRating'] * 100) / 5;
                    }
                }
            }
        }
        return $returnData;
    }

    public function countCollegeReviews($ci)
    {
        $this->db->select('*');
        $this->db->where("college_id", $ci);
        $query =  $this->db->get('review')->num_rows();
        //echo $this->db->last_query();exit;
        return $query;
    }
    public function getRevById($id)
    {
        $this->db->where('review_id', $id);
        $query = $this->db->get('review');
        $result = $query->row();
        return $result;
    }

    public function voteReview($reviewid, $userArr, $ishelpful)
    {
        if ($ishelpful == 1) {
            $this->db->set('helpful_yes_counter', 'helpful_yes_counter+1', FALSE);
        } else {
            $this->db->set('helpful_no_counter', 'helpful_no_counter+1', FALSE);
        }
        $this->db->set('voted_users', implode(",", $userArr));
        $this->db->where('review_id', $reviewid);
        $query = $this->db->update('review');
        return $query;
    }
    public function addReview($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }


    public function getPlacementRating($collegeId = '', $page = 1, $rate = '')
    {
        $this->db->where('college_id', $collegeId);
        $this->db->where('status', 1);
        if (!empty($rate) && (int)$rate > 0 && (int)$rate <= 5) {
            $this->db->where('placement_rate <=', $rate);
        }
        $data = $this->db->get('review')->result_array();
        $totalPlacementRate = 0;
        $placementCount = 0;
        $offset = ($page - 1) * 10;
        $one2two = $two2three = $three2four = $four2five = 0;

        if (!empty($data)) {
            foreach ($data as $review) {
                $totalPlacementRate += $review['placement_rate']; // Accumulating total placement rates
                $placementCount++;
                if ($review['placement_rate'] <= 2) {
                    $one2two++;
                } elseif ($review['placement_rate'] <= 3) {
                    $two2three++;
                } elseif ($review['placement_rate'] <= 4) {
                    $three2four++;
                } else {
                    $four2five++;
                }
            }
        }

        $totalPlacementRate = ($placementCount > 0) ? number_format($totalPlacementRate / $placementCount, 1) : 0;

        return [
            'totalPlacementRate' => $totalPlacementRate,
            'totalPlacementRateCount' => $totalPlacementRate * 5, // Multiplying by 5 to get total count
            'totalReview' => count($data),
            'placementCount' => $placementCount,
            'placementRate' => ($placementCount > 0) ? intval(($placementCount / count($data) * 100)) : 0,
            'one2twoRate' => ($one2two > 0) ? intval(($one2two / count($data) * 100)) : 0,
            'two2threeRate' => ($two2three > 0) ? intval(($two2three / count($data) * 100)) : 0,
            'three2fourRate' => ($three2four > 0) ? intval(($three2four / count($data) * 100)) : 0,
            'four2fiveRate' => ($four2five > 0) ? intval(($four2five / count($data) * 100)) : 0,
            'reviews' => $this->getCollegeReviews($collegeId, 10, $offset, '', 'DESC', '', '', $rate),
        ];
    }


    public function getInfrastructureRating($collegeId = '', $page = 1, $rate = '')
    {
        $this->db->where('college_id', $collegeId);
        $this->db->where('status', 1);
        if (!empty($rate) && (int)$rate > 0 && (int)$rate <= 5) {
            $this->db->where('infrastructure_rate <=', $rate);
        }
        $data = $this->db->get('review')->result_array();
        $totalInfrastructureRate = 0;
        $one2two = $two2three = $three2four = $four2five = 0;

        if (!empty($data)) {
            foreach ($data as $review) {
                $totalInfrastructureRate += $review['infrastructure_rate'];
                if ($review['infrastructure_rate'] <= 2) {
                    $one2two++;
                } elseif ($review['infrastructure_rate'] <= 3) {
                    $two2three++;
                } elseif ($review['infrastructure_rate'] <= 4) {
                    $three2four++;
                } else {
                    $four2five++;
                }
            }
        }

        $totalInfrastructureRate = ($totalInfrastructureRate > 0) ? number_format($totalInfrastructureRate / count($data), 1) : 0;
        $offset = ($page - 1) * 10;

        return [
            'totalInfrastructureRate' => $totalInfrastructureRate,
            'totalInfrastructureRateCount' => $totalInfrastructureRate * 5,
            'totalReview' => count($data),
            'one2twoRate' => ($one2two > 0) ? intval(($one2two / count($data) * 100)) : 0,
            'two2threeRate' => ($two2three > 0) ? intval(($two2three / count($data) * 100)) : 0,
            'three2fourRate' => ($three2four > 0) ? intval(($three2four / count($data) * 100)) : 0,
            'four2fiveRate' => ($four2five > 0) ? intval(($four2five / count($data) * 100)) : 0,
            'infraReviews' => $this->getCollegeReviews($collegeId, 10, $offset, '', 'DESC', '', '', $rate),
        ];
    }


    public function getRatingList()
    {
        $rating = [
            ['rate' => '2-3', 'count' => 0],
            ['rate' => '3-4', 'count' => 0],
            ['rate' => '4-5', 'count' => 0]
        ];
        $this->db->select('c.id AS college_id,  c.package_type,c.application_link, logo,c.is_accept_entrance, c.title, banner, estd, ci.city, g.image, (CASE WHEN c.package_type = "featured_listing" THEN 0 ELSE 1 END) AS sort_order');
        $this->db->from('college c');
        $this->db->join('city ci', 'ci.id = c.cityid', 'left');
        $this->db->join('gallery g', 'g.postid = c.id', 'left');
        $this->db->where('c.is_deleted', '0');
        $this->db->where('c.status', '1');
        $this->db->where('g.type', 'college');
        // $this->db->group_by('c.id');
        // $this->db->order_by('sort_order', 'asc');
        $query = $this->db->get();
        $result = $query->result_array();
        // print_r($result);exit;
        $totalColleges = count($result);
        if (!empty($result)) {
            $clgIds = array_column($result, 'college_id');
            $reviewBreakup = $this->getReviewBreakup($clgIds);

            foreach ($reviewBreakup as $review) {
                if ($review['totalRating'] >= 2 && $review['totalRating'] < 3) {
                    $rating[0]['count'] += 1;
                } elseif ($review['totalRating'] >= 3 && $review['totalRating'] < 4) {
                    $rating[1]['count'] += 1;
                } elseif ($review['totalRating'] >= 4 && $review['totalRating'] <= 5) {
                    $rating[2]['count'] += 1;
                }
            }
        }
        return $rating;
    }
}
