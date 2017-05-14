<?php

/** @file 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\currency\Controller;

use Drupal\Core\Database\Connection;

class CurrencyNameFetch {

  protected $connection;

  /**
   * 
   * @param Connection $connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * 
   * @param type $url
   * @param type $from_currency
   * @param type $to_currency
   * @param type $amount
   */
  public function collectingData($from_currency, $to_currency, $amount) {
    $url = 'http://www.google.com/finance/converter?a=' . $amount . '&from=' . $from_currency . '&to=' . $to_currency;
    $client = \Drupal::httpClient()->get($url, array('headers' => array('Accept' => 'text/plain')));
    $data = (string) $client->getBody();
    return $data;
  }

  /**
   * It will fetch all the currency name and code from the database and return the array.
   * @return array
   */
  public function getInfo() {
    $result = $this->connection->select('curreny_converter', 'c')
            ->fields('c', ['CurrencyCode', 'CurrencyName'])
            ->execute()->fetchAll();
    $temp = json_decode(json_encode($result), TRUE);
    $arr = [];
    foreach ($temp as $val) {
      $arr[$val['CurrencyCode']] = $val['CurrencyName'];
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
  public function currencyApi($from_Currency, $to_Currency, $amount) {

    //it will check whether the currency configuration google api selected or not.
    if (\Drupal::config('currency.converter')->get('selection') == 'Google Currency Converter API') {
      $data = $this->collectingData($from_Currency, $to_Currency, $amount);
      //it will match the content and save it into the differency variable.
      preg_match("/<span class=bld>(.*)<\/span>/", $data, $currencycheck);
      $result = explode(" ", $currencycheck[1]);
      return $result[0];
    }// it will check whether user selected to use the database currency checker.
    elseif (\Drupal::config('currency.converter')->get('selection') == 'Data Offline Handling') {

      $res = $this->connection->select('currency_offlne_data', 'c')
              ->fields('c', ['price'])
              ->condition('destination_currency', $to_Currency, '=')
              ->condition('date', Date('Y-m-d'), '=')
              ->execute()->fetchAll();
      $result = json_decode(json_encode($res), TRUE);
      $res = $this->connection->select('currency_offlne_data', 'c')
              ->fields('c', ['price'])
              ->condition('destination_currency', $from_Currency, '=')
              ->condition('date', Date('Y-m-d'), '=')
              ->execute()->fetchAll();
      $resultsecond = json_decode(json_encode($res), TRUE);
      return ((1 / $resultsecond[0]['price']) * $result[0]['price']) * $amount;
    }//if the user did not select any thing from the currency convertor configuration it will send the error.
    else {
      return 'Please Select the Currency Convertor API /admin/config/system/currency';
    }
  }

  /**
   * This function is used to inform the user that the you have selected this
   * currency which are used to display in the front page of the website
   * @return array
   */
  public function getCheck() {
    $options = $this->getInfo();
    $check = \Drupal::config('currency.converter')->get('selecti');
    $arr = [];
    foreach ($check as $key => $value) {
      if ($value != ' ') {
        $arr[$key] = $value;
      }
    }
    $ar = [];
    foreach ($arr as $key => $value) {
      foreach ($options as $keys => $value) {
        if ($key == $keys) {
          $ar[$key] = $value;
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
  public function createGraph($from, $to) {

    //Getting the Price and date of the Source Currency
    $fromarray = $this->connection->select('currency_offlne_data', 'cod')
            ->fields('cod', ['price', 'date'])
            ->condition('destination_currency', $from, '=')
            ->orderBy('cod.date', 'DESC')
            ->range(0, 4)
            ->execute()->fetchAll();
    $from_array_result = json_decode(json_encode($fromarray), TRUE);
    //Getting the Price and date of the destination currency
    $toarray = $this->connection->select('currency_offlne_data', 'cod')
            ->fields('cod', ['price', 'date'])
            ->condition('destination_currency', $to, '=')
            ->orderBy('cod.date', 'DESC')
            ->range(0, 4)
            ->execute()->fetchAll();
    //converting the data into the array.
    $to_array_result = json_decode(json_encode($toarray), TRUE);
    $newarray = [];
    $count = 0;
    //creating a new array in the below steps.
    for ($i = sizeof($from_array_result) - 1; $i >= 0; $i--) {
      $newarray[$count]['price'] = $to_array_result[$i]['price'] / $from_array_result[$i]['price'];
      $newarray[$count]['date'] = date("d", strtotime($to_array_result[$i]['date']));
      $count++;
    }
    //unsetting all the variable 
    unset($from_array_result);
    unset($toarray);
    unset($to_array_result);
    unset($fromarray);
    $new_json = json_encode($newarray);
    //returning the json data to the FrontPanel file.
    return $new_json;
  }

}
