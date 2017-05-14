<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\currency\Controller;

use Drupal\Core\Database\Connection;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CurrencyControllerApi extends ControllerBase {

  /**
   *
   * @var database object 
   */
  protected $database;

  /**
   * 
   * @param Connection $database
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * 
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return \static
   */
  public static function create(ContainerInterface $container) {

    return new static(
        $container->get('database')
    );
  }

  /**
   * This function will  help the outside user to hit the url and get
   *  the json data.
   * @param type $from
   * @param type $to
   * @param type $amount
   * @return JsonResponse
   */
  public function apiHit($from, $to, $amount) {
    //This will get the information of the user selected currency, the $to variable hold the information.
    $resi = $this->database->select('currency_offlne_data', 'c')
            ->fields('c', ['price'])
            ->condition('destination_currency', $to, '=')
            ->condition('date', Date('Y-m-d'), '=')
            ->execute()->fetchAll();
    //changing the data into the array format.
    $result = json_decode(json_encode($resi), TRUE);
    //This will get the information of the user selected from currency, the $from variable hold the information.
    $res = $this->database->select('currency_offlne_data', 'c')
            ->fields('c', ['price'])
            ->condition('destination_currency', $from, '=')
            ->condition('date', Date('Y-m-d'), '=')
            ->execute()->fetchAll();
    //changing the data into the array format.
    $resultsecond = json_decode(json_encode($res), TRUE);
    //returning the result of the changing currency into json format.
    return new JsonResponse(['Data' => ((1 / $resultsecond[0]['price']) * $result[0]['price']) * $amount]);
  }

}
