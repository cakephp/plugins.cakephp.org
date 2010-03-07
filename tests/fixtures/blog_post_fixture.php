<?php
/* BlogPost Fixture generated on: 2010-03-07 01:03:52 : 1267926772 */
class BlogPostFixture extends CakeTestFixture {
	var $name = 'BlogPost';

	var $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'key' => 'primary'),
		'slug' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 100, 'key' => 'index'),
		'title' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 90),
		'content' => array('type' => 'text', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), 'slug' => array('column' => 'slug', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	var $records = array(
		array(
			'id' => 1,
			'slug' => 'first-post',
			'title' => 'First Post!',
			'content' => 'Hello all, welcome to \"CakePackages.com\":http://cakepackages.com. CakePackages is a website built for new and veteran CakePHP developers who wish to quickly find pre-built CakePHP code, whether it be a plugin, application, or even just some utility.\n\n_*Why*_ did I build this? Because I was _bored_ :) Actually, it\'s so that my developers no longer have to come to me when they want to find a bit of pre-built CakePHP code for their applications. Good times.\n\nWhere is this application even going? Well, for now it will simply serve as an alternative to searching Google. In the coming weeks, I will be opening up account maintenance to developers, as well as allowing others to tag existing code. There is also a scheduled import of new code at the end of the week. If you have something not listed, send a direct message to \"@cakepackages\":http://twitter.com/cakepackages on twitter and I\'ll be sure to set you right up.\n\nFeel free to browse the code on \"Github\":http://github.com/josegonzalez/cakepackages . Want to have a specific feature implemented? Fork and submit a pull request. \n\nSome stats about the app:\n\n# This blog is just written in textile\n# There is a custom component controlling access to the application called the @PermitComponent@.\n# Custom @TaggingBehavior@ as well :)\n# The Search functionality is powered by \"Neil Crookes\'\":http://www.neilcrookes.com/ \"SearchablePlugin\":http://github.com/neilcrookes/searchable . It has some patches to make setting up URLs to packages easier. I\'ll be contributing those back to the plugin in the future.\n# 491 code packages and counting. Expect a dozen or more new packages in the next import\n# We\'re tracking 189 developers\n# ~101 of those have emails available on github\n# New York has the most indexed CakePHP developers. We rule :D\n\n*Follow this space for more updates*'
		),
	);
}
?>