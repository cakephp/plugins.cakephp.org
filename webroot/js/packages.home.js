// case insensitive :contains
$.extend($.expr[":"], {
    "containsIN": function(elem, i, match) {
        return (elem.textContent || elem.innerText || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
    }
});

$(function() {

    var $table = $('table:eq(0)');

    // sorting
    var dir = -1;
    $table.find('th').click(function() {
        dir = -dir;
        var rows = $table.find('tbody tr').detach();
        if ($(this).text() == 'Watchers') {
            rows.sort(function(a,b){
                return dir * (parseInt($(b).find('.watchers').text()) - parseInt($(a).find('.watchers').text()));
            });
        } else {
            rows.sort(function(a,b) {
                return $(a).find('.package-name').text() <  $(b).find('.package-name').text() ? dir : -dir;
            });
        }
        rows.each(function() {
            $table.find('tbody').append($(this));
        });
    });

    // instant search
    $('#query').keyup(function() {
        var s = $(this).val();
        if (s) {
            $table.find('tbody tr').hide();
            $table.find('tbody tr:containsIN(' + s + ')').show();
        } else {
            $table.find('tbody tr').show()
        }
    });

});
