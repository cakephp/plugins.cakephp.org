jQuery(function () {
  var cache = {},
      url = "/packages/autocomplete",
      lastXhr;
  jQuery("#SearchIndexTerm").autocomplete({
    minLength: 2,
    delay: 125,
    html: true,
    source: function(request, response) {
      var term = request.term;
      if (term in cache) {
        response(cache[term]);
        return;
      }

      lastXhr = jQuery.getJSON(url, request, function(data, status, xhr) {
        cache[ term ] = data;
        if (xhr === lastXhr) {
          response(data);
        }
      });
    },
    select: function (event, ui) {
      window.location.href = '/package/' + ui.item.slug;
    }
  });
  jQuery('.tooltip').tipsy({fade: true});
  jQuery('.tooltip_w').tipsy({fade: true, gravity: 'w'});
  jQuery('.tooltip_w a').tipsy({fade: true, gravity: 'w'});
});