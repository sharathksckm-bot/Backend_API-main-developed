
var currentStep = 1;
var updateProgressBar;

function displayStep(stepNumber) {
  if (stepNumber >= 1 && stepNumber <= 3) {
    $(".step-" + currentStep).hide();
    $(".step-" + stepNumber).show();
    currentStep = stepNumber;
    updateProgressBar();
  }
}

$(document).ready(function () {
  $("#multi-step-form").find(".step").slice(1).hide();

  $(".next-step").click(function () {
    if (currentStep < 3) {
      $(".step-" + currentStep).addClass(
        "animate__animated animate__fadeOutLeft"
      );
      currentStep++;
      setTimeout(function () {
        $(".step").removeClass("animate__animated animate__fadeOutLeft").hide();
        $(".step-" + currentStep)
          .show()
          .addClass("animate__animated animate__fadeInRight");
        updateProgressBar();
      }, 500);
    }
  });

  $(".prev-step").click(function () {
    if (currentStep > 1) {
      $(".step-" + currentStep).addClass(
        "animate__animated animate__fadeOutRight"
      );
      currentStep--;
      setTimeout(function () {
        $(".step")
          .removeClass("animate__animated animate__fadeOutRight")
          .hide();
        $(".step-" + currentStep)
          .show()
          .addClass("animate__animated animate__fadeInLeft");
        updateProgressBar();
      }, 500);
    }
  });

  updateProgressBar = function () {
    var progressPercentage = ((currentStep - 1) / 2) * 100;
    $(".progress-bar").css("width", progressPercentage + "%");
  };
});

///........................................................................................................................///
function setCookie(name, value, days) {
	var expires = "";
	if (days) {
			var date = new Date();
			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
			expires = "; expires=" + date.toUTCString();
	}
	document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Function to get a cookie value
function getCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') c = c.substring(1, c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return null;
}

$(document).ready(function() {
	var updatedamount1 = parseFloat(getCookie("finalAmount")) || 0;
	$("#totalamount").text("$" + updatedamount1.toFixed(2));
	var finalQuantity = getCookie("finalQuantity");
	if (finalQuantity === null) {
		finalQuantity = 0;
	}
	document.getElementById("item").innerText = "(" + finalQuantity + " item)";	
	var productDetails = getCookie('productDetails');
	var productDetailsFromCookie = [
    {"name":"Beam - AWE Pass","price":100,"quantity":"2"},
    {"name":"Beam1 - AWE Pass","price":200,"quantity":"1"}
];
	generateTableRows(JSON.parse(productDetails));
	console.log('productDetailsFromCookie',productDetailsFromCookie);

	console.log(productDetails);

});

function generateTableRows(productDetails) {
	var tbody = document.querySelector('tbody');
	tbody.innerHTML = '';
	productDetails.forEach(function(product) {
    var row = document.createElement('tr');

    var nameCell = document.createElement('td');
    nameCell.innerHTML = '<b>' + product.name + '</b>';
    row.appendChild(nameCell);

    var priceCell = document.createElement('td');
    priceCell.textContent = '$' + product.price.toFixed(2);
    row.appendChild(priceCell);

		var quantityCell = document.createElement('td');
		var countBox = document.createElement('div');
		countBox.className = 'countbox';
		var minusIcon = document.createElement('i');
		minusIcon.className = 'bi bi-dash-square-fill';
		minusIcon.onclick = function() {
				decrementValue(product);
		};
		countBox.appendChild(minusIcon);
		var input = document.createElement('input');
		input.type = 'text';
		input.className = 'countinput';
		input.value = product.quantity;
		countBox.appendChild(input);
		var plusIcon = document.createElement('i');
		plusIcon.className = 'bi bi-plus-square-fill';
		countBox.appendChild(plusIcon);
		quantityCell.appendChild(countBox);
		row.appendChild(quantityCell);


    var totalPriceCell = document.createElement('td');
    totalPriceCell.textContent = '$' + (product.price * product.quantity).toFixed(2);
    row.appendChild(totalPriceCell);

    var deleteCell = document.createElement('td');
    deleteCell.innerHTML = '<i class="bi bi-x-square-fill closex"></i>';
    row.appendChild(deleteCell);
		deleteCell.querySelector('i').addEventListener('click', function() {
			row.parentNode.removeChild(row);
		});
    tbody.appendChild(row);
});
function decrementValue(product) {
	console.log(product)
	if (product.quantity > 0) {
			product.quantity--;
			generateTableRows(productDetails);
	}
}

var totalPrice = 0;
productDetails.forEach(function(product) {
    totalPrice += product.price * product.quantity;
});
var totalPayment = totalPrice + 100;

var shippingRow = document.createElement('tr');
shippingRow.innerHTML = '<td colspan="2"></td><td class="font-bold">Shipping Cost</td><td colspan="2">$100</td>';
tbody.appendChild(shippingRow);

var totalPaymentRow = document.createElement('tr');
totalPaymentRow.innerHTML = '<td colspan="2"></td><td class="font-bold">Total Payment</td><td colspan="2">$' + totalPayment.toFixed(2) + '</td>';
tbody.appendChild(totalPaymentRow);

var methodRow = document.createElement('tr');
methodRow.className = 'methodbox';
methodRow.innerHTML = '<td colspan="5"><div class="row methodbox"><div class="col-sm-2 col-md-3"><h5>Method of payment</h5></div><div class="col-sm-2 col-md-3"><div class="form-check"><input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1"><label class="form-check-label" for="flexRadioDefault1">Credit Card</label></div><div class="form-check"><input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" checked><label class="form-check-label" for="flexRadioDefault2">Voucher</label></div></div><div class="col-sm-2 col-md-3"><input type="text" class="form-control" placeholder="Enter Promo Code"></div><div class="col-sm-2 col-md-3"><button type="button" class="btn bgblue text-white next-step">Apply Discount</button></div></div></td>';
tbody.appendChild(methodRow);

}




