$(document).ready(function(e) {
    $("aside a").click(function (event) {
        event.preventDefault();
        var destino = 0;
        if ($(this.hash).offset().top > $(document).height() - $(window).height()) {
            destino = $(document).height() - $(window).height();
        } else {
            destino = $(this.hash).offset().top;
        }

        $('html,body').animate({
            scrollTop: destino
        }, 2000, 'swing');
    });
});