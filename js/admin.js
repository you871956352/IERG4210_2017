(function () {

    function updateUI(val) {

        myLib.get({
            action: 'cat_fetchall'
        }, function (json) {
            // loop over the server response json
            //   the expected format (as shown in Firebug): 
            for (var options = [], listItems = [],
                    i = 0, cat; cat = json[i]; i++) {
                options.push('<option value="', parseInt(cat.catid), '">', cat.name.escapeHTML(), '</option>');
            }
            el('prod_catid_' + val).innerHTML = '<option></option>' + options.join('');
        });
    }

    $('.myform').addClass('hide');

    $('#choose').change(function () {
        var selector = '.myform_' + $(this).val();

        $('.myform').removeClass('show');

        $(selector).addClass('show');

        if ($(this).val() != '3' && $(this).val() != '-1') {
            updateUI($(this).val());
            if($(this).val() == '2'){
                updateUI('2n');
            }
        }
    });

    $('#prod_catid_1').change(function () {
        var cat = $(this).val();

        if (cat == '') {
            el('prod_name_1').innerHTML = '<option></option>';
        } else {
            myLib.post({
                action: 'prod_fetchByCat',
                catID: cat,
            }, function (json) {
                for (var listItems = [], i = 0, pro; pro = json[i]; i++) {
                    listItems.push('<option value="', parseInt(pro.pid), '">', pro.name.escapeHTML(), '</option>');
                }
                el('prod_name_1').innerHTML = '<option></option>' + listItems.join('');
            });
        }
    });

    $('#prod_catid_2').change(function () {
        var cat = $(this).val();

        if (cat == '') {
            el('prod_name_2').innerHTML = '<option></option>';
        } else {
            myLib.post({
                action: 'prod_fetchByCat',
                catID: cat,
            }, function (json) {
                for (var listItems = [], i = 0, pro; pro = json[i]; i++) {
                    listItems.push('<option value="', parseInt(pro.pid), '">', pro.name.escapeHTML(), '</option>');
                }
                el('prod_name_2').innerHTML = '<option></option>' + listItems.join('');
            });
        }
    });
})();
