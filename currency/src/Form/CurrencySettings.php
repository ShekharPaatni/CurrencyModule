<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Drupal\currency\Form;
use \Drupal\Core\Ajax\AjaxResponse;
use \Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

class CurrencySettings extends ConfigFormBase {
  
  /**
   * 
   * @return type
   */
  protected function getEditableConfigNames() {
    return ['currency.converter'];
  }
/**
 * 
 * @return string
 */
  public function getFormId() {
    return 'currency_converter';
  }
/**
 * This function will build the form.
 * @param array $form
 * @param FormStateInterface $form_state
 * @return array $form
 */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $options= \Drupal::service('currency.fetch_data')->getInfo();
    $form=parent::buildForm($form, $form_state);
    $form['Currency_Converter_API']=[
      '#type' => 'details',
      '#title' => $this->t('Currency Converter API'),
      '#open' => TRUE
    ];
    $form['Currency_Converter_API']['selection']=[
      '#type' => 'select',
      '#options' => ['Select Currency API' => 'Select Currency API',
        'Google Currency Converter API' => 'Google Currency Converter API',
        'Data Offline Handling' => 'Data Offline Handling'],
      '#default_value' => $this->config('currency.converter')->get('selection'),
      '#weight' => -30,
      '#ajax' => [
        'callback' => '::checkSelected',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => 'Fetching correspondence data'
          ],
       ],
       
    ];
    $form['Currency_Converter_API']['APILink']=[
      '#type' => 'container',
      '#prefix' => '<div id="currencyAPI">',
      '#suffix' => '</div>',
      '#weight' => -10
    ];
   
    $form['Selection']=[
      '#type' => 'checkboxes',
      '#title' => $this->t('Select Currency you want to display:'),
      '#options' => $options,
      '#default_value' => $this->config('currency.converter')->get('selecti'),
    ];
    $form['#attached']['library'][]='currency/currency-check';
    
   return $form;
    
  }
  /**
   * This function will save the data into the config table.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('currency.converter')->set('selection',$form_state->getValue('selection'))->save();
    $this->config('currency.converter')->set('selecti', $form_state->getValue('Selection'))->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * This function is the basically call on submit and save the configuration.
   * @param array $form
   * @param FormStateInterface $form_state
   * @return AjaxResponse
   */
  public function checkSelected(array &$form, FormStateInterface &$form_state){
   $response=new AjaxResponse();
   $form_state->setRebuild(TRUE);
   
   if($form_state->getValue('selection')== 'Google Currency Converter API'){
      $form['Currency_Converter_API']['APILink']['show']=[
        '#type' => 'textfield',
        '#title' => $this->t('API Link'),
        '#size' => 80,
        '#id' => 'currency_check',
        '#attributes' =>['readonly' => 'readonly'],
        '#value' => $this->t("http://www.google.com/finance/converter?a=\$amount&from=\$from_Currency&to=\$to_Currency"),
        '#weight' => -10
        ];
    $response->addCommand(new \Drupal\Core\Ajax\AppendCommand("#currencyAPI",$form['Currency_Converter_API']['APILink']['show']));
   }elseif('Data Offline Handling' == $form_state->getValue('selection')){
       $form['Currency_Converter_API']['APILink']=[
      '#type' => 'container',
      '#prefix' => '<div id="currencyAPI">',
      '#suffix' => '</div>',
       '#weight' => -10  
      
    ];
      $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#currency_check",$form['Currency_Converter_API']['APILink']));
   }else{
     $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand("#currency_check","Please Select the Currency Convertor API /admin/config/system/currency"));
   }
   return $response;
   
  }

}