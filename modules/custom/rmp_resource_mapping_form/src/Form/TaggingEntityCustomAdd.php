<?php

namespace Drupal\rmp_resource_mapping_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_collection\Entity\FieldCollection;
use Drupal\field_collection\Entity\FieldCollectionItem;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Class TaggingEntityCustomAdd.
 *
 * @package Drupal\rmp_resource_mapping_form\Form
 */
class TaggingEntityCustomAdd extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tagging_entity_custom_add';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $project_id = null) {
    
    if(!empty($project_id) && is_numeric($project_id)) {
      $entity_manager = \Drupal::entityTypeManager();
      $project = $entity_manager->getStorage('project')->load($project_id);
      if ($project) {
        $project_name_data = $project->get("name")->getValue();
        $pursuit_id_data = $project->get("pursuit_id")->getValue();
        $client_id_data = $project->get("client_id")->getValue();
        $sbu_data = $project->get("sbu")->getValue();
        $reporting_manager_data = $project->get("reporting_manager")->getValue();
        $fte_data = $project->get("fte")->getValue();
        $start_date_data = $project->get("start_date")->getValue();
        $end_date_data = $project->get("end_date")->getValue();

        //Project name is same has pursuit name
        $project_name = isset($project_name_data[0])? $project_name_data[0]['value']: '';
        $pursuit_id = isset($pursuit_id_data[0])? $pursuit_id_data[0]['target_id']: '';
        $client_id = isset($client_id_data[0])? $client_id_data[0]['target_id']: '';
        $sbu_tid = isset($sbu_data[0])? $sbu_data[0]['target_id']: '';
        $field_collection_item_ids = $project->get("field_project_code_collection")->getValue();
        $reporting_manager = isset($reporting_manager_data[0])? $reporting_manager_data[0]['target_id']: '';
        $fte = isset($fte_data[0])? $fte_data[0]['value']: '';
        $start_date = isset($start_date_data[0])? $start_date_data[0]['value']: date('Y-m-d', time());
        $end_date = isset($end_date_data[0])? $end_date_data[0]['value']: '';

        $client_name = '';
        if ($client_id) {
          $referencedClientEntity = $project->get('client_id')->first()->get('entity')->getTarget()->getValue();
          $referencedClientEntity_name_data = $referencedClientEntity->get("name")->getValue();
          $client_name = isset($referencedClientEntity_name_data[0])? $referencedClientEntity_name_data[0]['value']: '';
        }
        $sbu = '';
        if ($sbu_tid) {
          $referencedSbuEntity = $project->get('sbu')->first()->get('entity')->getTarget()->getValue();
          $referencedSbuEntity_name_data = $referencedSbuEntity->get("name")->getValue();
          $sbu = isset($referencedSbuEntity_name_data[0])? $referencedSbuEntity_name_data[0]['value']: '';
        }
        $reporting_manager_name = '';
        if ($reporting_manager) {
          $referencedReportingManagerEntity = $project->get('reporting_manager')->first()->get('entity')->getTarget()->getValue();
          $referencedReportingManagertEntity_name_data = $referencedReportingManagerEntity->get("name")->getValue();
          $reporting_manager_name = isset($referencedReportingManagertEntity_name_data[0])? $referencedReportingManagertEntity_name_data[0]['value']: '';
        }
        //Form
        $form['pursuit_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Pursuit Name'),
          '#maxlength' => 128,
          '#size' => 64,
          '#default_value' => $project_name,
          '#attributes' => array('readonly' => 'readonly'),
        ];
        $form['project_id'] = array(
          '#type' => 'hidden',
          '#value' => $project_id,
        );
        $form['pursuit_id'] = array(
          '#type' => 'hidden',
          '#value' => $pursuit_id,
        );
        $form['client_name'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Client Name'),
          '#maxlength' => 128,
          '#size' => 64,
          '#default_value' => $client_name,
          '#attributes' => array('readonly' => 'readonly'),
        ];
        $form['client_id'] = array(
          '#type' => 'hidden',
          '#value' => $client_id,
        );
        $form['sbu'] = [
          '#type' => 'textfield',
          '#title' => $this->t('SBU'),
          '#maxlength' => 128,
          '#size' => 64,
          '#default_value' => $sbu,
          '#attributes' => array('readonly' => 'readonly'),
        ];
        $form['sbu_tid'] = array(
          '#type' => 'hidden',
          '#value' => $sbu_tid,
        );
        $form['reporting_manager'] = [
          '#type' => 'select',
          '#title' => $this->t('Reporting Manager'),
          '#options' => [$reporting_manager => $reporting_manager_name],
          '#default_value' => $reporting_manager,
          '#attributes' => array('readonly' => 'readonly', 'disabled' => 'disabled'),
        ];
        //Project code and project name
        $tag_code[''] = 'None';
        $j = 1;
        foreach ($field_collection_item_ids as $field_collection_item) {
          $fc_item = FieldCollectionItem::load($field_collection_item['value']);
          $field_project_code_data = $fc_item->get("field_project_code")->getValue();
          $field_project_name_data = $fc_item->get("field_project_name")->getValue();
          $project_tag_code = isset($field_project_code_data[0])? $field_project_code_data[0]['value']: '';
          $project_tag_name = isset($field_project_name_data[0])? $field_project_name_data[0]['value']: '';

          $form['project_fc'][]['common_project_code_' . $j] = [
            '#type' => 'textfield',
            '#title' => $this->t('Project Code'),
            '#maxlength' => 128,
            '#size' => 64,
            '#default_value' => $project_tag_code,
            '#attributes' => array('readonly' => 'readonly'),
          ];
          $form['project_fc'][]['common_project_name_' . $j] = [
            '#type' => 'textfield',
            '#title' => $this->t('Project Name'),
            '#maxlength' => 128,
            '#size' => 64,
            '#default_value' => $project_tag_name,
            '#attributes' => array('readonly' => 'readonly'),
          ];
          $tag_code[$project_tag_code] = $project_tag_code;
          $tag_name[$project_tag_code] = $project_tag_name;
          $j++;
        }

        //Resource Details
        $form['resource_details'] = array(
          '#type' => 'fieldset',
          '#title' => $this->t('Resource Details'),
          '#collapsed' => FALSE,
        );
        //get the resource_status taxonomy terms
        $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('resource_status', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
        $resource_status_bench_id = '';
        foreach ($terms as $term) {
          if ($term->name == 'Bench' || $term->name == 'Blocked') {
            $resource_status_bench_id = $term->tid;
          }
        }

        //Get all resources in Bench
        $resources = \Drupal::entityTypeManager()->getListBuilder('user')->getStorage()->loadMultiple();
        $recommendations[''] = $this->t('None');
        foreach ($resources as $resource) {
          $current_status_data = $resource->get("field_current_status")->getValue();
          $current_status = isset($current_status_data[0])? $current_status_data[0]['target_id']: '';
          if ($current_status == $resource_status_bench_id || $current_status == '') {
            $roles = $resource->get("roles")->getValue();
            $user_id = $resource->id();
            //Authenticated user role not getting.
            //Drupal 8:  $user->uid != NULL (Internally: $user->uid > 0). A special authenticated user role exists.
            if (empty($roles) && $user_id > 1 ) {
              $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
            }
            if (!empty($roles) && count($roles) == 1 ) {
              $primary_role = $roles[0]['target_id'];
              //Exclude roles from recommendations
              $exculde_roles = array('anonymous','administrator');
              if (!in_array($primary_role, $exculde_roles)) {
                $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
              }
            }
            else if (!empty($roles) && count($roles) > 1) {
              $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
            }
          }
        }

        //Create each form row
        for ($i=1;$i<= 1;$i++) {
          $form['resource_details'][$i]['#prefix'] = '<div class="col-md-12"><div class="resource-data" id="id-resource-data-' . $i . '">';
          $form['resource_details'][$i]['resource_startdate_' . $i] = array(
            '#type' => 'date',
            '#title' => $this->t('Start Date'),
            '#default_value' => $start_date,
          );
          $form['resource_details'][$i]['resource_enddate_' . $i] = array(
            '#type' => 'date',
            '#title' => $this->t('End Date'),
            '#default_value' => '',
          );
          $tag_value = 0;
          $form['resource_details'][$i]['resource_tag_' . $i] = array(
            '#type' => 'checkbox',
            '#title' => t('Tag'),
            '#default_value' => $tag_value,
          );

          $primary_skills[''] = $this->t('None');
          //get the primary_skills taxonomy terms
          $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('primary_skills', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
          foreach ($terms as $term) {
            $primary_skills[$term->tid] = $term->name;
          }
          $form['resource_details'][$i]['resource_skill_' . $i] = array(
            '#type' => 'select',
            '#title' => $this->t('Skill'),
            '#default_value' => '',
            '#options' => $primary_skills,
            '#ajax' => [
              'callback' => '::AjaxCallback',
              'wrapper' => 'state-wrapper' . $i,
              'effect' => 'fade',
              'message' => $this->t('Please wait...'),
            ],
          );

          $office_locations[''] = $this->t('None');
          //get the office_locations taxonomy terms
          $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('office_locations', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
          foreach ($terms as $term) {
            $office_locations[$term->tid] = $term->name;
          }
          $form['resource_details'][$i]['resource_location_' . $i] = array(
            '#type' => 'select',
            '#title' => $this->t('Location'),
            '#default_value' => '',
            '#options' => $office_locations,
            '#ajax' => [
              'callback' => '::AjaxCallback',
              'wrapper' => 'state-wrapper' . $i,
              'effect' => 'fade',
              'message' => $this->t('Please wait...'),
            ],
          );

          $grades[''] = $this->t('None');
          //get the grades taxonomy terms
          $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('grades', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
          foreach ($terms as $term) {
            $grades[$term->tid] = $term->name;
          }
          $form['resource_details'][$i]['resource_grade_' . $i] = array(
            '#type' => 'select',
            '#title' => $this->t('Grade'),
            '#default_value' => '',
            '#options' => $grades,
            '#ajax' => [
              'callback' => '::AjaxCallback',
              'wrapper' => 'state-wrapper' . $i,
              'effect' => 'fade',
              'message' => $this->t('Please wait...'),
            ],
          );
          $form['resource_details'][$i]['resource_recommendations_' . $i] = array(
            '#type' => 'select',
            '#title' => $this->t('Recommendations'),
            '#default_value' => '',
            '#options' => $recommendations,
            '#required' => TRUE,
            '#default_value' => '',
            '#attributes' => [
              'id' => 'state-wrapper' . $i,
            ],
            '#prefix' => '<div class="resource-recommendations-add-' . $i . '">',
            '#suffix' => '</div>',
          );
          $form['resource_details'][$i]['resource_sonumber_' . $i] = array(
            '#type' => 'textfield',
            '#title' => $this->t('SO'),
            '#maxlength' => 20,
            '#size' => 20,
            '#default_value' => '',
          );
          $form['resource_details'][$i]['resource_tagcode_' . $i] = array(
            '#type' => 'select',
            '#title' => $this->t('Tag code'),
            '#options' => $tag_code,
            '#default_value' => '',
          );
        }
        //Submit
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];
        //Form theme
        $form['#theme'] = 'tagging_entity_custom_add_form';
      }
      else {
        throw new NotFoundHttpException();
      }
    }

    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $project_to_resources_mapping = '';
    foreach ($form_state->getValues() as $key => $value) {
      //drupal_set_message($key . ': ' . $value);
      $exclude_key = array('submit', 'form_build_id', 'form_token', 'form_id', 'op');
      if (!in_array($key, $exclude_key)) {
        $pos = strpos($key, "resource");
        if ($pos === false) {
          //Common data
          $common_data[$key] = $value;
        } else {
          $resource_key = explode('_', $key);
          $row = $resource_key[2];
          $field = $resource_key[1];
          $project_to_resources_mapping[$row][$field] = $value;
        }
      }
    }
    foreach($project_to_resources_mapping as $key => $resource) {
      foreach($common_data as $common_key => $common) {
        $project_to_resources_mapping[$key][$common_key] = $common;
      }
    }

    if (!empty($project_to_resources_mapping)) {
      //Save mapping to tagging entity
      $currentUser = \Drupal::currentUser()->id();

      //get the grades taxonomy terms
      $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('tagging_status', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
      foreach ($terms as $term) {
        $tagging_status[$term->name] = $term->tid;
      }

    //Save mapping to tagging entity
      foreach ($project_to_resources_mapping as $key => $values) {
        $tag = $values['tag'];
        $tag_value = '';
        if ($tag == 0) {
          $tag_status = 'Blocked';
        }
        else if ($tag == 1) {
          $tag_status = 'Tagged';
        }
        $tag_value = $tagging_status[$tag_status];
        $tagging_values = array(
          'user_id' => $currentUser,
          'name' => $values['pursuit_name'],
          'project_tag_code' => $values['tagcode'],
          'pursuit_id' => $values['pursuit_id'],
          'project_id' => $values['project_id'],
          'start_date' => $values['startdate'],
          'end_date' => $values['enddate'],
          'resource_id' => !empty($values['recommendations'])? $values['recommendations']: null,
          'reporting_manager_id' => $values['reporting_manager'],
          'client_id' => $values['client_id'],
          'so_code' => $values['sonumber'],
          'tagging_status' => $tag_value,
          'skill' => $values['skill'],
          'location' => $values['location'],
          'grade' => $values['grade'],
          'active' => 1,
          'created' => time(),
          'changed' => time(),
          );
        $tagging = entity_create('tagging', $tagging_values);
        $tagging->save();
        $tagging_id = $tagging->id();
        $project_id = $values['project_id'];
        \Drupal::logger('rmp_tagging')->debug('Tagging added for project code ---> ' . $values['tagcode']);
        drupal_set_message('Tagging done successfully for ' . $values['pursuit_name']);
      }
      //Redirect to resource_mapping page
      $url = Url::fromUri('internal:/admin/structure/resource_mapping/' . $project_id)->toString();
      $response = new RedirectResponse($url);
      $response->send();
    }
  }

  /**
   * {@AjaxCallback}
   */
  public function AjaxCallback(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#name'])) {
      $pos = strpos($triggering_element['#name'], "resource");
      if ($pos === false) {
        //common fields, don't do anything
      } else {
        $resource_key = explode('_', $triggering_element['#name']);
        $row = $resource_key[2];
        $field = $resource_key[1];

        //get the resource_status taxonomy terms
        $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadTree('resource_status', $parent = 0, $max_depth = NULL, $load_entities = FALSE);
        $resource_status_bench_id = '';
        foreach ($terms as $term) {
          if ($term->name == 'Bench'|| $term->name == 'Blocked') {
            $resource_status_bench_id = $term->tid;
          }
        }
        //Get all resources in Bench
        $resources = '';
        $resource_skill = $form_state->getValue('resource_skill_' . $row);
        $resource_location = $form_state->getValue('resource_location_' . $row);
        $resource_grade = $form_state->getValue('resource_grade_' . $row);
        if (!empty($resource_skill) || !empty($resource_location) || !empty($resource_grade)) {
          //Build QUERY
          $query = \Drupal::entityQuery('user');
          if (!empty($resource_skill)) {
            $query->condition('field_primary_skill', $resource_skill);
          }
          if (!empty($resource_location)) {
            $query->condition('field_location', $resource_location);
          }
          if (!empty($resource_grade)) {
            $query->condition('field_grade', $resource_grade);
          }
          $query->condition('status', 1);
          $query->sort('name', 'ASC');
          $resources_ids = $query->execute();
          if (!empty($resources_ids)) {
            $resources = \Drupal::entityTypeManager()->getListBuilder('user')->getStorage()->loadMultiple($resources_ids);
          }
        }
        else {
          $resources = \Drupal::entityTypeManager()->getListBuilder('user')->getStorage()->loadMultiple();
        }
        $recommendations = '';
        $recommendations[''] = $this->t('None');
        if (!empty($resources)) {
          foreach ($resources as $resource) {
            $current_status_data = $resource->get("field_current_status")->getValue();
            $current_status = isset($current_status_data[0])? $current_status_data[0]['target_id']: '';
            if ($current_status == $resource_status_bench_id || $current_status == '') {
              $roles = $resource->get("roles")->getValue();
              $user_id = $resource->id();
              //Authenticated user role not getting.
              //Drupal 8:  $user->uid != NULL (Internally: $user->uid > 0). A special authenticated user role exists.
              if (empty($roles) && $user_id > 1 ) {
                $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
              }
              if (!empty($roles) && count($roles) == 1 ) {
                $primary_role = $roles[0]['target_id'];
                //Exclude roles from recommendations
                $exculde_roles = array('anonymous','administrator');
                if (!in_array($primary_role, $exculde_roles)) {
                  $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
                }
              }
              else if (!empty($roles) && count($roles) > 1) {
                $recommendations[$resource->id()] = $resource->get("name")->getValue()[0]['value'];
              }
            }
          }
        }
        //Append saved resource to recommendations
        /*$saved_user = $form_state->getValue('resource_recommendations_' . $row);
        $res_id = isset($saved_user)? $saved_user: '';
        if (!empty($res_id)) {
          $user = \Drupal::entityTypeManager()->getStorage('user')->load($res_id);
          if (!empty($user)) {
            $user_name_data = $user->get("name")->getValue();
            $user_name = isset($user_name_data[0])? $user_name_data[0]['value']: '';
            if (!empty($user_name)) {
              $user_id = $user->id();
              $recommendations[$user_id] = $user_name;
            }
          }
        }*/
        //Recommendations
        $form['resource_details'][$row]['resource_recommendations_' . $row] = array(
          '#type' => 'select',
          '#title' => $this->t('Recommendations'),
          '#default_value' => '',
          '#options' => $recommendations,
          '#attributes' => [
            'id' => 'state-wrapper' . $row,
            'name' => 'resource_recommendations_' . $row,
          ],
          '#prefix' => '<div class="resource-recommendations-add-' . $row . '">',
          '#suffix' => '</div>',
        );
      }
    }
    //Ajax response
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand(
      '.resource-recommendations-add-' . $row,$form['resource_details'][$row]['resource_recommendations_' . $row]));

    return $response;
  }

}
