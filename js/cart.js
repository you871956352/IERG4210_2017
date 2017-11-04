function reload_Cart() {
    var tempCart;
    if (localStorage.cart != undefined)
        tempCart = JSON.parse(localStorage.cart);
    else {
        var cart = {};
        localStorage.cart = JSON.stringify(cart);
        tempCart = JSON.parse(localStorage.cart);
    };

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
    content += "<button class=\"dropdown-item cko\" id=\"checkout\" onclick='checkOut()'>Checkout</button>";
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

function checkOut() {
    //Further defined.
}
