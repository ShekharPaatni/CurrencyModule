<?php

/*
 * @file
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\currency\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;

class FrontPanel extends FormBase {

  /**
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array $form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $service = \Drupal::service('currency.fetch_data');
    //This will fetch only those country currency name which are selected by you in /admin/config/system/currency.
    $options = $service->getCheck();
    $form['amount'] = [
      '#type' => 'number',
      '#size' => 40,
      '#title' => $this->t('Enter Amount'),
      '#attributes' => ['id' => 'ConversionAmount'],
      '#required' => TRUE,
      '#default_value' => 0
    ];
    $form['from'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Your Currency From'),
      '#options' => $options,
    ];
    $form['to'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Your Currency To'),
      '#options' => $options,
      '#attributes' => ['id' => 'currency_from'],
    ];
    $form['submission'] = [
      '#type' => 'submit',
      '#value' => 'Convert',
      '#ajax' => [
        'callback' => '::gettingData',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => 'Converting wait'
        ],
      ]
    ];
    $form['result'] = [
      '#type' => 'container',
      '#prefix' => '<div id="result"></div>',
    ];

    $form['graph'] = [
      '#type' => 'container',
      '#prefix' => '<div id="graphResult"></div>',
    ];
    $form['#attached']['library'][] = 'currency/currency-check';
    return $form;
  }

  /**
   * This function will call when the currency converter convert button will click it will fetch the data. 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function gettingData(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $service = \Drupal::service('currency.fetch_data');
    $response = new AjaxResponse();
    //it will fetch all the currency code and name.
    $options = $service->getInfo();
    //Fetching the data from the block placed area.
    $amount = $form_state->getValue('amount');
    $to = $form_state->getValue('to');
    $from = $form_state->getValue('from');
    //condition will check whether the number must be greater than 0 and response accordingly.
    if ($amount > 0) {
      if ($to != NULL) {
        //In this condition will check whether both currency are equals or not.
        if ($to != $from) {
          //It will compute the result and return and save it to $res variable.
          $res = $service->currencyApi($from, $to, $amount);
          if (\Drupal::config('currency.converter')->get('selection') != 'Select Currency API') {
            $result = $options[$to] . " is equals to " . $res . ".";
            $response->addCommand(new HtmlCommand('#result', $result));
            $response->addCommand(new RemoveCommand('#graphResult > div'));
            $response->addCommand(new RemoveCommand('#genrateGraph'));
//          If the selected API is equals to Data Offline Handling so then the graph will appear else it 
//          will not create.
            if (\Drupal::config('currency.converter')->get('selection') == 'Data Offline Handling') {
              $response->addCommand(new AfterCommand('#result', '<svg id="genrateGraph" height=250px width=100%></svg>'));
              $response->addCommand(new AppendCommand('#graphResult', $service->createGraph($from, $to)));
            }
          }
          else {
            $response->addCommand(new HtmlCommand('#result', $res));
          }
        }
        else {
          $response->addCommand(new RemoveCommand('#graphResult > div'));
          $response->addCommand(new RemoveCommand('#genrateGraph'));
          $response->addCommand(new HtmlCommand('#result', 'Please select different currency both currency are same.'));
        }
      }
      else {
        $response->addCommand(new HtmlCommand('#result', 'Please select the currencies from /admin/config/system/currency'));
      }
    }
    else {
      $response->addCommand(new RemoveCommand('#graphResult > div'));
      $response->addCommand(new RemoveCommand('#genrateGraph'));
      $response->addCommand(new HtmlCommand('#result', 'The amount should be greater than zero.'));
    }


    return $response;
  }

  /**
   * 
   * @return string
   */
  public function getFormId() {
    return "frontpanel";
  }

  /**
   * 
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    
  }

  public function validateForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

}
