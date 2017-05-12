<?php

/** @file 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Drupal\currency\Controller;
use Drupal\Core\Controller\ControllerBase;

class CurrencyNameFetch extends ControllerBase{
  /**
   * It will fetch all the currency name and code from the database and return the array.
   * @return array
   */
  public static function getInfo(){
      
      $result=\Drupal::database()->select('curreny_converter','c')
        ->fields('c',['CurrencyCode','CurrencyName'])
        ->execute()->fetchAll();
      $temp= json_decode(json_encode($result),TRUE);
      $arr=[];
          foreach ($temp as $val){
            $arr[$val['CurrencyCode']]=$val['CurrencyName'];
          }  
  return $arr;
  }
  /**
   * This will use in the currency convertor configuration settings to inform
   * user to select the api if it is not yet selected.
   * @param String $from_Currency 
   * @param String $to_Currency
   * @param int $amount
   * @return string/int
   */
  public static function currencyApi($from_Currency, $to_Currency, $amount){
     
    //it will check whether the currency configuration google api selected or not.
    if(\Drupal::config('currency.converter')->get('selection')=="Google Currency Converter API"){
      $url="http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";
      $data= file_get_contents($url); 
      preg_match("/<span class=bld>(.*)<\/span>/", $data, $currencycheck);
      $result= explode(" ", $currencycheck[1]); 
      return $result[0];
    }// it will check whether user selected to use the database currency checker.
    elseif(\Drupal::config('currency.converter')->get('selection')=='Data Offline Handling'){
       
              $res= \Drupal::database()->select('currency_offlne_data','c')
                ->fields('c',['price'])
                ->condition('destination_currency',$to_Currency, '=')
                ->condition('date',Date('Y-m-d'),'=')
                ->execute()->fetchAll();
              $result=json_decode(json_encode($res),TRUE);
              $res= \Drupal::database()->select('currency_offlne_data','c')
                ->fields('c',['price'])
                ->condition('destination_currency',$from_Currency, '=')
                ->condition('date',Date('Y-m-d'),'=')
                ->execute()->fetchAll();
              $resultsecond=json_decode(json_encode($res),TRUE);
              return ((1/$resultsecond[0]['price'])*$result[0]['price'])*$amount;
    }//if the user did not select any thing from the currency convertor configuration it will send the error.
    
    else{
          return "Please Select the Currency Convertor API /admin/config/system/currency";
        }
}
  /**
   * This function is used to inform the user that the you have selected this
   * currency which are used to display in the front page of the website
   * @return array
   */
  public static function getCheck(){
    $options=\Drupal::service('currency.fetch_data')->getInfo();
    $check=\Drupal::config('currency.converter')->get('selecti');
    $arr=[];
    foreach ($check as $key=>$value){
      if($value!=' '){
      $arr[$key]=$value;
      }
    }
      $ar=[];
      foreach ($arr as $key => $value){  
       foreach($options as $keys=>$value){
        if($key==$keys){
          $ar[$key]=$value;
        }
        }
      } 
      return $ar;
}
/**
 * This is the function which will call on the time of the graph creation 
 * on submit. 
 * @param String $from
 * @param String $to
 * @return Json
 */
 public static function createGraph($from,$to){
   $con=\Drupal::database();
   //Getting the Price and date of the Source Currency
   $fromarray=$con->select('currency_offlne_data','cod')
                ->fields('cod',['price', 'date'])
                ->condition('destination_currency',$from,'=')
                ->orderBy('cod.date','DESC')
                ->range(0,4)
                ->execute()->fetchAll();
  $from_array_result= json_decode(json_encode($fromarray),TRUE); 
  //Getting the Price and date of the destination currency
   $toarray=$con->select('currency_offlne_data','cod')
                ->fields('cod',['price', 'date'])
                ->condition('destination_currency',$to,'=')
                ->orderBy('cod.date','DESC')
                ->range(0,4)
                ->execute()->fetchAll();
   $to_array_result= json_decode(json_encode($toarray),TRUE);
   $newarray=[];
   $count=0;
   for($i= sizeof($from_array_result)-1;$i>=0;$i--){
     $newarray[$count]['price']=$to_array_result[$i]['price']/$from_array_result[$i]['price'];
     $newarray[$count]['date']=date("d", strtotime($to_array_result[$i]['date']));
     $count++;
     
   }
   $new_json= json_encode($newarray);
   
   return $new_json;
 }

}
  