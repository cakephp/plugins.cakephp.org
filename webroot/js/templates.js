window.templates = {

	loader: function () {
		return '<img src="/img/ajax-loader.gif" class="loading" />';
	},

	maintainer_link: function (p) {
		return [
			'<a href="/package/' + p.Maintainer.username + '/' + p.Package.name + '" title="' + p.Package.name + '">',
				p.Package.name,
			'</a>'
		].join("\n");
	},

	nextPage: function (params) {
		if (params === false ) {
			return window.templates.noMoreResults();
		}

		return [
			'<span class="next-page">',
				'<a href="/packages?' + $.fn.serializeParams(params) + '" rel="next">Load More</a>',
			'</span>'
		].join("\n");
	},

	noMoreResults: function () {
		return '<div class="end-pagination">End of results</div>';
	},

	package_listing: function (p) {
		return [
			'<article>',
				'<div class="article">',
					'<div class="preview">',
						'<h3>' + window.templates.maintainer_link(p) + '</h3>',
						'<div class="info">',
							'<p class="description">' + p.Package.description + '</p>',
							'<div class="details">',
								'<strong>By:</strong> ',
								'<a href="/maintainer/' + p.Maintainer.username + '" class="author">',
									p.Maintainer.username,
								'</a>',
								'<span class="date">',
									'<strong>Added On:</strong> ',
									p.Package.created,
								'</span>',
							'</div>',
						'</div>',
					'</div>',
				'</div>',
			'</article>'
		].join("\n");
	}

};
