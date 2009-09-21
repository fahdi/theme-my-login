jQuery(document).ready(function($){

    $("#container, #container div").tabs({ fx: { opacity: 'toggle', duration: 'fast' } });
    
    $("a.link").live("click", function() {
        var arr = $(this).attr("class").split(" ");
        var action = arr[1];
        var role = arr[2];
        var lastrow = $("#links-" + role + " tbody>tr:last").attr("id");
        var count = Number(lastrow.substring(9, lastrow.length)) + 1;
        if ('add' == action) {
            $("#links-" + role + " tbody>tr:last").clone(true).insertAfter("#links-" + role + " tbody>tr:last");
            var curclass = $("#links-" + role + " tbody>tr:last").attr("class");
            var newclass = ('alternate' == curclass) ? '' : 'alternate';
            $("#links-" + role + " tbody>tr:last").attr("class", newclass);
            $("#links-" + role + " tbody>tr:last").attr("id", "link-row-" + count);
            $("#links-" + role + " tbody>tr:last input.link-title").attr("id", "links[" + role + "][" + count + "][title]");
            $("#links-" + role + " tbody>tr:last input.link-title").attr("name", "links[" + role + "][" + count + "][title]");
            $("#links-" + role + " tbody>tr:last input.link-url").attr("id", "links[" + role + "][" + count + "][url]");
            $("#links-" + role + " tbody>tr:last input.link-url").attr("name", "links[" + role + "][" + count + "][url]");
            $("#links-" + role + " tbody>tr:last input.link-title").attr("value", "");
            $("#links-" + role + " tbody>tr:last input.link-url").attr("value", "");
            $("#links-" + role + " tbody>tr:last input.link-title").focus();
            updateButtons();
        } else {
            $(this).parent().parent().parent().remove();
            updateButtons();
        }
        return false;
    });
    updateButtons();
});

function updateButtons() {
    jQuery(".link-table a.link.remove").show();
    jQuery(".link-table a.link.add").hide();
    jQuery(".link-table").each(function(i) {
        jQuery("#" + this.id + " a.link.add:last").show();
    });
}
