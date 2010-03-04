<?php
class BlogPostsController extends BlogAppController {
	var $name = 'BlogPosts';
	var $helpers = array('Blog.Textile');

	function index() {
		$blogPosts = $this->paginate();
		$this->set(compact('blogPosts'));
	}

	function view($slug = null) {
		$blogPost = $this->BlogPost->findBySlug($slug);
		if (!$blogPost) {
			$this->Session->setFlash(sprintf(__('Invalid %s', true), 'blog post'));
			$this->redirect(array('action' => 'index'));
		}
		$this->set(compact('blogPost'));
	}
}
?>