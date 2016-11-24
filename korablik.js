var arr = [];
arr[123] = {
    count: 12,
    price: 45.88
};
arr[345] = {
    count: 2,
    price: 66.33
};
arr[900] = {
    count: 10,
    price: 35
};

function total_amount(arr) {
    var amount = 0;
    if (arr.length > 0) {
        arr.forEach(function (item, index, array) {
            amount += item.count * item.price;
        });
    }
    console.log(amount);
    return amount;
};

window.document.write('<a href="https://yandex.ru" onclick="return confirm(\'Сумма заказа: \' + total_amount(arr));">Покинуть страницу</a>');