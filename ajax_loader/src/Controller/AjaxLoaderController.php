<?php
namespace Drupal\ajax_loader\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Controller\ControllerBase;

class AjaxLoaderController extends ControllerBase{

  public function customAjaxLoader($id) {
	$node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    // node render
    if ($node) {
        $node_render = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'ajax_viewer');
        $node_render = render($node_render);
    } else {
		$node_render = 'Что то пошло не так!';
	}

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $node_render));

	return $response;
  }

  public function customAjaxLoaderEdit($id) {
	$node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    // node render
    if ($node) {
		$formObject = \Drupal::entityTypeManager()->getFormObject('node', 'default')->setEntity($node);
        $form = \Drupal::formBuilder()->getForm($formObject);
    } else {
		$form = 'Что то пошло не так!';
	}

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxLoaderDelete($id) {
	$node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
    // node render
    if ($node) {
		$formObject = \Drupal::entityTypeManager()->getFormObject('node', 'delete')->setEntity($node);
        $form = \Drupal::formBuilder()->getForm($formObject);
    } else {
		$form = 'Что то пошло не так!';
	}

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxProjectEdit($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\project\Form\EditProjectForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxProjectDelete($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\project\Form\DeleteProjectForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxJobAdd($object_id, $project_id) {

	//query for stages and jobs
	$query = \Drupal::entityQuery('node');
	$query->condition('status', 1);
	$query->condition('field_project_id', $project_id);
	$query->condition('type', 'project_stage');
	$result = $query->execute();
	//end query
	if ($result) {
  		$items =  \Drupal\node\Entity\Node::loadMultiple($result);
  		foreach ($items as $item) {
			//stages array for jobs
            $stageArr[$item->id()] = $item->getTitle();
		}
	}
	
	$params = [
		'object_id' => $object_id,
		'project_id' => $project_id,
		'stageArr' => $stageArr,
	];
	$form = \Drupal::formBuilder()->getForm('Drupal\job\Form\CreateJobForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxJobEdit($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\job\Form\EditJobForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxJobDelete($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\job\Form\DeleteJobForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxStageAdd($object_id, $project_id) {

	$params = [
		'object_id' => $object_id,
		'project_id' => $project_id,
	];
	$form = \Drupal::formBuilder()->getForm('Drupal\stage\Form\CreateStageForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxStageEdit($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\stage\Form\EditStageForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  public function customAjaxStageDelete($id) {
	
	$params['id'] = $id;
	$form = \Drupal::formBuilder()->getForm('Drupal\stage\Form\DeleteStageForm', $params);

	$selector = '#ajaxModalBody';

	$response = new AjaxResponse();
	$response->addCommand(new HtmlCommand($selector, $form));

	return $response;
  }

  

}