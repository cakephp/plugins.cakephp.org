<?php
class MaintainersController extends AppController {

/**
 * The name of this controller. Controller names are plural, named after the model they manipulate.
 *
 * @var string
 * @access public
 * @link http://book.cakephp.org/view/959/Controller-Attributes
 */
    var $name = 'Maintainers';

/**
 * Paginates the current maintainers
 */
    function index() {
        $this->paginate = array('index');
        $maintainers = $this->paginate();
        $this->set(compact('maintainers'));
    }

/**
 * Allows the viewing of a user
 *
 * @param string $username Username
 */
    function view($username = null) {
        try {
            $this->set('maintainer', $maintainer = $this->Maintainer->find('view', $username));
        } catch (Exception $e) {
            $this->_flashAndRedirect($e->getMessage());
        }
    }

/**
 * Allows editing of a maintainer by id
 *
 * @param string $id 
 */
    function edit($id = null) {
        $this->_redirectUnless($id, __('Invalid maintainer', true));
        $this->_redirectUnless($this->data, __('Invalid maintainer', true));

        if (!empty($this->data)) {
            if ($this->Maintainer->save($this->data)) {
                $this->_flashAndRedirect(__('The maintainer has been saved', true));
            } else {
                $this->Session->setFlash(__('The maintainer could not be saved. Please, try again.', true));
            }
        }

        if (empty($this->data)) {
            try {
                $this->data = $this->Maintainer->find('edit', $id);
            } catch (Exception $e) {
                $this->_flashAndRedirect($e->getMessage());
            }

            $this->_redirectUnless($this->data);
        }
    }

/**
 * Allows deleting a maintainer by id
 *
 * @param string $id 
 */
    function delete($id = null) {
        $this->_redirectUnless($id);

        $message = __('Maintainer was not deleted', true);
        if ($this->Maintainer->delete($id)) {
            $message = __('Maintainer deleted', true);
        }

        $this->_flashAndRedirect($message);
    }

}