<?php

namespace App\Services;


use App\Client;
use Gmopx\LaravelOWM\LaravelOWM;
use Rinvex\Country\Country;

class ClientService
{
    /**
     * @var LaravelOWM
     */
    private $weather;

    /**
     * @var Country|array
     */
    private $country;

    public function __construct(LaravelOWM $weather)
    {
        $this->weather = $weather;
    }

    /**
     * @param array $parameters
     * @return false|string
     */
    public function create(array $parameters)
    {
        foreach ($parameters['users'] as $user) {
            $this->country = country($user['country']);

            Client::create([
                'name' => $user['name'],
                'phone' => $user['phone'],
                'country_iso' => $user['country'],
                'capital' => $this->country->getCapital(),
                'weather' => $this->getWeather(),
            ]);
        }
    }

    /**
     * @return string
     */
    private function getWeather()
    {
        $currentWeather =  $this->weather->getCurrentWeather($this->country->getCapital());

        return $currentWeather->weather->description;
    }
}