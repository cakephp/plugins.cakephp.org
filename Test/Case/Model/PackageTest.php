<?php
App::uses('Package', 'Model');
App::uses('Folder', 'Utility');
App::uses('HttpSocket', 'Network/Http');

/**
 * Package Test Case
 *
 */
class PackageTestCase extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.package',
		'app.user',
		'app.maintainer',
		'app.user_detail',
		'app.tag',
		'app.tagged',
		'app.category',
		'plugin.ratings.rating',
		'plugin.favorites.favorite',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Package = ClassRegistry::init('Package');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Package);
		$Folder = new Folder(TMP . DS . 'repos');
		$Folder->delete();
		parent::tearDown();
	}

/**
 * testSetupRepository method
 *
 * @return void
 */
	public function testSetupRepository() {
		$Package = $this->getMock('Package', array('_shell_exec'));
		$Package->useDbConfig = 'test';
		$Package->expects($this->exactly(2))
			->method('_shell_exec')
			->will($this->returnValue('success'));
		$result = $Package->setupRepository(1);
		$expected = array(1, '/var/www/cakepackages/app/tmp/repos/s/shama/chocolate');
		$this->assertEquals($expected, $result);
		$this->assertTrue( file_exists(dirname($result[1])) );

	}
/**
 * testBroken method
 *
 * @return void
 */
	public function testBroken() {
		$this->Package->broken(1);
		$result = $this->Package->findById(1);
		$this->assertFalse($result);
	}

/**
 * testEnable
 *
 * @return void
 */
	public function testEnable() {
		$result = $this->Package->findById(1);
		$this->assertFalse(empty($result));

		// DISABLE
		$this->Package->enable(1);
		$result = $this->Package->findById(1);
		$this->assertTrue(empty($result));

		// ENABLE
		$this->Package->enable(1);
		$result = $this->Package->findById(1);
		$this->assertFalse(empty($result));

		// MANUAL DISABLE
		$this->Package->enable(1, false);
		$result = $this->Package->findById(1);
		$this->assertTrue(empty($result));
	}

/**
 * testCharacterize method
 *
 * @return void
 */
	public function testCharacterize() {
		$Package = $this->getMock('Package', array('_shell_exec'));
		$Package->expects($this->any())
			->method('_shell_exec')
			->will($this->returnValue('success'));

		$repo = $Package->setupRepository(1);
		mkdir($repo[1], 0755, true);

		$result = $Package->characterize(1);
		$this->assertTrue(count($result['Package']) > 1);
	}
/**
 * testFixRepositoryUrl method
 *
 * @return void
 */
	public function testFixRepositoryUrl() {
		$result = $this->Package->fixRepositoryUrl(1);
		$this->assertEquals('git://github.com/shama/chocolate.git', $result['Package']['repository_url']);
	}
/**
 * testCategorizePackage method
 *
 * @return void
 */
	public function testCategorizePackage() {
		$data = array(
			'Package' => array(
				'id' => 1,
				'category_id' => 99,
			),
		);
		$result = $this->Package->categorizePackage($data);
		$this->assertEquals(1, $result);
		$result = $this->Package->findById(1);
		$this->assertEquals(99, $result['Package']['category_id']);
	}
/**
 * testFavoritePackage method
 *
 * @return void
 */
	public function testFavoritePackage() {
		$result = $this->Package->favoritePackage(1, 1);
		$this->assertTrue($result);
		$result = $this->Package->favoritePackage(1, 2);
		$result = $this->Package->Favorite->find('all', array(
			'conditions' => array(
				'model' => 'Package',
				'foreign_key' => 1,
				'type' => 'bookmark',
			),
		));
		$this->assertEquals(2, count($result));
	}
/**
 * testRatePackage method
 *
 * @return void
 */
	public function testRatePackage() {
		$result = $this->Package->ratePackage(1, 1);
		$this->assertTrue($result);
		$result = $this->Package->ratePackage(1, 1);
		$this->assertFalse($result);

		$result = $this->Package->ratePackage(1, 1);
		$result = $this->Package->Rating->find('first', array(
			'conditions' => array(
				'model' => 'Package',
				'foreign_key' => 1,
				'user_id' => 1,
			),
		));
		$this->assertEquals(1, $result['Rating']['value']);
	}
/**
 * testUpdateAttributes method
 *
 * @return void
 */
	public function testUpdateAttributes() {
		$this->Package->_Github = $this->getMock('Github', array('find'));
		$this->Package->_Github
			->expects($this->at(0))
			->method('find')
			->with($this->equalTo('reposShowSingle'))
			->will($this->returnValue(array(
				'Repository' => array(
					'url' => 'http://github.com/shama/chocolate',
					'homepage' => 'http://shama.github.com/newchocolate',
					'has_issues' => true,
					'open_issues' => 99,
					'forks' => 99,
					'watchers' => 99,
					'created_at' => '2012-12-31 04:42:44',
					'pushed_at' => '2012-12-31 04:42:44',
				),
			)));
		$this->Package->_Github
			->expects($this->at(1))
			->method('find')
			->with($this->equalTo('reposShowContributors'))
			->will($this->returnValue(array()));
		$this->Package->_Github
			->expects($this->at(2))
			->method('find')
			->with($this->equalTo('reposShowCollaborators'))
			->will($this->returnValue(array()));
		$this->Package->contain('Maintainer');
		$package = $this->Package->findById(1);
		$result = $this->Package->updateAttributes($package);
		$this->assertEquals('http://shama.github.com/newchocolate', $result['Package']['homepage']);
		$this->assertEquals(99, $result['Package']['open_issues']);
		$this->assertEquals(99, $result['Package']['watchers']);
		$this->assertEquals(99, $result['Package']['forks']);
		$this->assertEquals('2012-12-31 04:42:44', $result['Package']['created_at']);
		$this->assertEquals('2012-12-31 04:42:44', $result['Package']['last_pushed_at']);
	}
/**
 * testFindOnGithub method
 *
 * @return void
 * @todo Finish with Github model
 */
	public function testFindOnGithub() {
		$this->Package->_Github = $this->getMock('Github', array('find'));
		$this->Package->_Github
			->expects($this->once())
			->method('find')
			->with($this->equalTo('reposShowSingle'))
			->will($this->returnValue(array(
				'Repository' => array(
					'url' => 'http://github.com/shama/chocolate',
					'homepage' => 'http://shama.github.com/newchocolate',
					'has_issues' => true,
					'open_issues' => 99,
					'forks' => 99,
					'watchers' => 99,
					'created_at' => '2012-12-31 04:42:44',
					'pushed_at' => '2012-12-31 04:42:44',
				),
			)));
		$result = $this->Package->findOnGithub(1);
		$this->assertTrue($result);
	}
/**
 * testCleanParams method
 *
 * @return void
 */
	public function testCleanParams() {
		$data = array(
			'watchers' => 5,
			'forks' => 3,
			'notallowed' => 'bad!'
		);
		$result = $this->Package->cleanParams($data, array(
			'allowed' => Package::$_allowedFilters,
			'coalesce' => true,
		));
		$expected = array(
			array('watchers' => 5, 'forks' => 3),
			'watchers:5 forks:3',
		);
		$this->assertEquals($expected, $result);
	}
/**
 * testCategories method
 *
 * @return void
 */
	public function testCategories() {
		$result = $this->Package->categories(1);
		$this->assertEquals(43, count($result));
		$this->assertTrue(in_array('Email', $result));
		$this->assertTrue(in_array('Uncategorized', $result));
		$result = $this->Package->categories();
		$this->assertEquals(43, count($result));
		$this->assertEquals(43, count($this->Package->_categories));
	}
/**
 * testSuggest method
 *
 * @return void
 */
	public function testSuggest() {
		$Package = $this->getMock('Package', array('load', 'enqueue'));
		$Package->expects($this->once())
			->method('load')
			->with(
				$this->equalTo('SuggestPackageJob'),
				$this->equalTo('shama'),
				$this->equalTo('chocolate')
			)
			->will($this->returnValue(true));
		$Package->expects($this->once())
			->method('enqueue')
			->will($this->returnValue(true));
		
		$data = array(
			'github' => 'http://github.com/shama/chocolate',
		);
		$result = $Package->suggest($data);
		$expected = array('shama', 'chocolate');
		$this->assertEquals($expected, $result);
	}
/**
 * testSeoView method
 *
 * @return void
 */
	public function testSeoView() {
		$this->Package->contain('Maintainer');
		$package = $this->Package->findById(1);
		$result = $this->Package->seoView($package);
		$expected = array(
			'chocolate by shama | CakePHP Plugins and Applications | CakePackages',
			'Lorem ipsum dolor sit amet - CakePHP Package on CakePackages',
			'chocolate, cakephp package, cakephp',
		);
		$this->assertEquals($expected, $result);
	}
/**
 * testRss method
 *
 * @return void
 */
	public function testRss() {
		$this->Package->contain('Maintainer');
		$package = $this->Package->findById(1);
		
		$response = array(
			'status' => array('code' => 200),
			'body' => '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" xml:lang="en-US">
  <id>tag:github.com,2008:/shama/chocolate/commits/master</id>
  <link type="text/html" rel="alternate" href="https://github.com/shama/chocolate/commits/master"/>
  <link type="application/atom+xml" rel="self" href="https://github.com/shama/chocolate/commits/master.atom"/>
  <title>Recent Commits to chocolate:master</title>
  <updated>2011-10-27T12:11:15-07:00</updated>
  <entry>
    <id>tag:github.com,2008:Grit::Commit/061d2f79d385704727a44e962c805b844b09b104</id>
    <link type="text/html" rel="alternate" href="https://github.com/shama/chocolate/commit/061d2f79d385704727a44e962c805b844b09b104"/>
    <title>added some chocolate</title>
    <updated>2011-10-27T12:11:15-07:00</updated>
    <media:thumbnail height="30" width="30" url="https://secure.gravatar.com/avatar/243bfe9fd2a0d2d1ee757308771d3c6d?s=30&amp;d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-140.png"/>
      <author>
        <name>Kyle Robinson Young</name>
        <uri>https://github.com/shama</uri>
      </author>
    <content type="html">
      &lt;pre>m Model/Datasource/FtpSource.php
m Test/Case/Model/Datasource/FtpSourceTest.php
&lt;/pre>
      &lt;pre style=\'white-space:pre-wrap;width:81ex\'>added some chocolate&lt;/pre>
    </content>
  </entry>
</feed>'
		);

		$this->Package->_HttpSocket = $this->getMock('HttpSocket', array('request'));
		$this->Package->_HttpSocket->response = $response;
		$this->Package->_HttpSocket->expects($this->once())
			->method('request')
			->with($this->equalTo(array(
				'uri' => 'https://github.com/shama/chocolate/commits/master.atom',
			)))
			->will($this->returnValue($response));

		$result = $this->Package->rss($package);
		$expected = array(
			array(
				'id' => 'tag:github.com,2008:Grit::Commit/061d2f79d385704727a44e962c805b844b09b104',
				'link' => 'https://github.com/shama/chocolate/commit/061d2f79d385704727a44e962c805b844b09b104',
				'title' => 'added some chocolate',
				'updated' => '2011-10-27T12:11:15-07:00',
			),
		);
		$this->assertEquals($expected, $result[0]);
	}
/**
 * testDisqus method
 *
 * @return void
 */
	public function testDisqus() {
		$this->Package->contain('Maintainer');
		$package = $this->Package->findById(1);
		$result = $this->Package->disqus($package);
		$expected = array(
			'disqus_shortname' => 'cakepackages',
			'disqus_identifier' => '1',
			'disqus_title' => 'chocolate by shama',
			'disqus_url' => 'http://cakepackages.dev/packages/view/shama/chocolate'
		);
		$this->assertEquals($expected, $result);
	}
/**
 * testGetNextPage method
 *
 * @return void
 */
	public function testGetNextPage() {
		$this->assertFalse($this->Package->getNextPage(array(), false));
		$result = $this->Package->getNextPage(array());
		$this->assertEquals(array('page' => 2), $result);
		$result = $this->Package->getNextPage(array('page' => 99));
		$this->assertEquals(array('page' => 100), $result);
	}

/**
 * testGetJobs
 *
 * @return void
 */
	public function testGetJobs() {
		$result = $this->Package->getJobs();
		$this->assertTrue(is_array($result));
	}

/**
 * testFireJob
 *
 * @return void
 */
	public function testFireJob() {
		$data = array(
			'job' => 'UserForgotPasswordJob',
			'user_id' => '4f471545-27a8-4ad7-89c9-1ec075f6eb26',
			'ip_address' => '127.0.0.1',
		);
		$user = $this->Package->Maintainer->User->findById($data['user_id']);
		$Package = $this->getMock('Package', array('load', 'enqueue'), array(
			$this->Package->id,
			$this->Package->useTable,
			$this->Package->useDbConfig,
		));
		$Package->alias = 'Package';
		$Package->expects($this->once())
			->method('load')
			->with(
				$this->equalTo('UserForgotPasswordJob'),
				$this->equalTo($user['User']),
				$this->equalTo('127.0.0.1')
			)
			->will($this->returnValue(true));
		$Package->expects($this->once())
			->method('enqueue')
			->will($this->returnValue(true));
		$Package->fireJob($data);
	}

/**
 * testFindIndex
 *
 * @return void
 */
	public function testFindIndex() {
		$query = array(
			'named' => array(
				'has' => 'model',
			),
		);
		$result = $this->Package->find('index', $query);
		$result = Set::extract('/Package/name', $result);
		$expected = array('chocolate', 'peanutbutter');
		sort($result);
		$this->assertEquals($expected, $result);

		$query = array(
			'named' => array(
				'query' => 'choco',
			),
		);
		$result = $this->Package->find('index', $query);
		$result = Set::extract('/Package/name', $result);
		$expected = array('chocolate');
		$this->assertEquals($expected, $result);

		$query = array(
			'named' => array(
				'watchers' => 5,
			),
		);
		$result = $this->Package->find('index', $query);
		$result = Set::extract('/Package/name', $result);
		$expected = array('chocolate');
		$this->assertEquals($expected, $result);

		$query = array(
			'named' => array(
				'category' => 'email',
				'has' => 'model',
			),
		);
		$result = $this->Package->find('index', $query);
		$result = Set::extract('/Package/name', $result);
		$expected = array('chocolate');
		$this->assertEquals($expected, $result);
	}

/**
 * testSaveTags
 *
 * @return void
 */
	public function testSaveTags() {
		CakeSession::write('Auth', array(
			'User' => array(
				'is_admin' => true,
			),
		));
		$this->Package->Behaviors->load('CakePackagesTaggable');
		$data = array(
			'Package' => array(
				'id' => 1,
				'maintainer_id' => 1,
				'name' => 'test',
				'tags' => 'tag1, tag2, tag3',
				'contains' => array(
					'app',
					'0',
					'component',
					'0',
					'0',
					'helper',
				),
			),
		);
		$result = $this->Package->save($data);
		$this->Package->contain(array('Tag'));
		$result = $this->Package->findById($result['Package']['id']);
		$expected = 'tag3, tag2, tag1';
		$this->assertEquals($expected, $result['Package']['tags']);
		$this->assertTrue($result['Package']['contains_app']);
		$this->assertTrue($result['Package']['contains_component']);
		$this->assertTrue($result['Package']['contains_helper']);
		CakeSession::delete('Auth');
	}

}
