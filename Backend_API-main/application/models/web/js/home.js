$(document).ready(function() {
    var updatedamount1 = parseFloat(getCookie("finalAmount")) || 0;
    $("#totalamount").text("$" + updatedamount1.toFixed(2));
	var finalQuantity = getCookie("finalQuantity");
	if (finalQuantity === null) {
		finalQuantity = 0;
	}
	document.getElementById("item").innerText = "(" + finalQuantity + " item)";	
	


});

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
let counter = 1;
const count = 0
function incrementValue(elementId) {
	value = parseInt(document.getElementById(elementId).value);
	value += counter;
	document.getElementById(elementId).value = value;
}
function decrementValue(elementId)
{
	value = parseInt(document.getElementById(elementId).value);
	if(value > 0){
		value -= counter;
	  }
	document.getElementById(elementId).value = value;
}

let cart = [];
function addItem(productNameId,price, quantity) {
	let productName = $("#" + productNameId).text();
	let item = {
        name: productName,
        price: price,
        quantity: quantity
    };

	cart.push(item);
    var currenttotalamount = parseFloat(document.getElementById("totalamount").innerText.replace("$", ""));
    var currentItemsElement = document.getElementById("item");
    var currentItemsText = currentItemsElement.innerText;
    var currentItems = parseInt(currentItemsText.match(/\d+/)[0]) || 0;
    var currentProductdetails = getCookie("productDetails");
	console.log(currentProductdetails);
	
    var totalAmount = price * quantity;
    var updatedamount = totalAmount + currenttotalamount;
	var updatedquantity = parseInt(quantity) + parseInt(currentItems);

    setCookie('finalAmount', updatedamount, 1);
	setCookie('finalQuantity', updatedquantity, 1);
    setCookie('productDetails', JSON.stringify(cart), 1);

    var finalAmount = parseFloat(getCookie("finalAmount")) || 0;
	var finalQuantity = parseFloat(getCookie("finalQuantity")) || 0;

    console.log('cookieamount', finalAmount);
    
    document.getElementById("totalamount").innerText = "$" + finalAmount.toFixed(2);
    document.getElementById("item").innerText = "(" + finalQuantity + " item)";
}
document.getElementById("cartbtn").onclick = function() {
	var CartContentControllerURL = '/Ecommerce/CartContent';
	   var xhr = new XMLHttpRequest();
        xhr.open("GET", "/Ecommerce/CartContent", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
				window.location.href = CartContentControllerURL;
            }
        };
        xhr.send();

};
