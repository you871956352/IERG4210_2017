function ClearCart() {
    localStorage.clear();
    reload_Cart();
}

function reload_Cart() {
    var tempCart;
    if (localStorage.cart != undefined)
        tempCart = JSON.parse(localStorage.cart);
    else {
        var cart = {};
        localStorage.cart = JSON.stringify(cart);
        tempCart = JSON.parse(localStorage.cart);
    };

    //The shown content.
    var content = "<table><tr><th>Product</th><th>Price</th><th>Count</th><th>subTotal</th></tr>";
    var total = 0;
    var totalForOnePro = 0;
    for (var p in tempCart) {
        totalForOnePro = tempCart[p].num * tempCart[p].price;
        content += "<tr><td>" + tempCart[p].name + "</td><td>$" + tempCart[p].price + "</td>" +
            "<td><input class=\"min\" type=\"button\" value=\"-\" onclick=\"updatePrice(" + p + ",this.nextElementSibling.value-1)\" />" +
            "<input class=\"count\" type=\"text\" readonly=\"readonly\" value=" + tempCart[p].num + " onchange=\"updatePrice(" + p + ",this.value)\" />" +
            "<input class=\"add\" type=\"button\" value=\"+\" onclick=\"updatePrice(" + p + ",Number(this.previousElementSibling.value)+1)\" /></td>" +
            "<td>$" + totalForOnePro + "</td>" +
            "<td><button class=\"dropdown-item cko\" id=\"remove\" onclick=\'removeProduct(" + p + ")\'>Remove</td></tr>";
        total += totalForOnePro;
    }
    content += "</table>";
    content += "<div class=\"dropdown-item total\">Total: $" + total + "</div>";
    //content += "<button class=\"dropdown-item cko\" id=\"checkout\" onclick='checkOut()'>Checkout</button>";

    //The hidden form.
    var form = "<form id=\"payForm\" action=\"https://www.sandbox.paypal.com/cgi-bin/webscr\" method=\"POST\" onsubmit=\"return cart_submit(" + total + ",this)\">";
    form += "<input type=\"hidden\" name=\"cmd\" value=\"_cart\">";
    form += "<input type=\"hidden\" name=\"upload\" value=\"1\">";
    form += "<input type=\"hidden\" name=\"business\" value=\"you871956352-facilitator@gmail.com\">";
    form += "<input type=\"hidden\" name=\"currency_code\" value=\"HKD\">";
    form += "<input type=\"hidden\" name=\"charset\"  value=\"utf-8\">";

    var list_num = 1;
    for (var p1 in tempCart) {
        form += "<input type=\"hidden\" name=\"item_name_" + list_num + "\" value=\"" + tempCart[p1].name + "\"  >";
        form += "<input type=\"hidden\" name=\"item_number_" + list_num + "\" value=\"" + p1 + "\" >";
        form += "<input type=\"hidden\" name=\"quantity_" + list_num + "\" value=\"" + tempCart[p1].num + "\" >";
        form += "<input type=\"hidden\" name=\"amount_" + list_num + "\" value=\"" + tempCart[p1].price + "\"  >";
        list_num += 1;
    }
    form += "<input type=\"hidden\" name=\"custom\" value=\"\">";
    form += "<input type=\"hidden\" name=\"invoice\" value=\"\">";
    form += "<input class=\"dropdown-item cko\" type=\"submit\" id=\"checkout\" value=\"Checkout\"></form> ";

    content += form;

    document.getElementById("cartUL").innerHTML = content;
}

reload_Cart();

function addToCart(pid) {
    myLib.get({
        action: 'prod_fetchByPid',
        pID: pid
    }, function (json) {
        var cart = localStorage.cart;
        if (cart == undefined)
            cart = {};
        else
            cart = JSON.parse(cart);
        if (cart[pid] == undefined)
            cart[pid] = {
                'num': 0
            };
        var name = json[0].name.escapeHTML();
        var price = json[0].price.escapeHTML();
        cart[pid].name = name;
        cart[pid].price = price;
        cart[pid].num = cart[pid].num + 1;
        localStorage.cart = JSON.stringify(cart);
        reload_Cart();
    });
}

function updatePrice(p, number) {
    var tempCart = JSON.parse(localStorage.cart);
    if (number > 0) {
        tempCart[p].num = number;
        localStorage.cart = JSON.stringify(tempCart);
    } else if (number == 0) {
        delete tempCart[p];
        localStorage.cart = JSON.stringify(tempCart);
    } else
        alert("Error on updating price !");
    reload_Cart();
}

function removeProduct(p) {
    var tempCart = JSON.parse(localStorage.cart);
    delete tempCart[p];
    localStorage.cart = JSON.stringify(tempCart);
    reload_Cart();
}

function ajaxSend() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            alert(xmlhttp.responseText);
            var obj = JSON.parse(xmlhttp.responseText);
            if (obj.ifLogin == 0)
                window.location.href = "login.php";
            else {
                var form = document.getElementById("payForm");
                form.elements.namedItem("invoice").value = obj.id;
                form.elements.namedItem("custom").value = obj.digest;
                form.submit();
                ClearCart();
            }
        }
    };

    xmlhttp.open("POST", "getOrder.php", true);
    //xmlhttp.setRequestHeader("Content-type",  "application/json");
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    var tempCart = JSON.parse(localStorage.cart);
    var pair = {};
    for (var tp in tempCart) {
        pair[tp] = tempCart[tp].num;
    }
    pair = JSON.stringify(pair);
    var message = "message=" + pair;
    alert(message);
    xmlhttp.send(message);
}

function cart_submit(total, e) {
    if (total == 0) {
        alert("No product to purchase !");
        return false;
    } else {
        ajaxSend();
    }
    return false;
}
