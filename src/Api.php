<?php

namespace Farrugia\Api;


use stdClass;

class Api
{
    private $url = '/';

    private $pathToCertificat = '';

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass|array
     * @throws CurlException|ApiException
     */
    public function post (string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'POST', $data, $headers);
    }

    /**
     * @param string $endPoint
     * @param string $method
     * @param array|null $data
     *
     * @param array $headers
     *
     * @return stdClass|array
     * @throws CurlException|ApiException
     */
    private function api (string $endPoint, string $method, array $data = [], array $headers = [])
    {
        $ch = curl_init($this->url . $endPoint);

        curl_setopt_array($ch, $this->generateOptions($method, $data, $headers));

        if (!($response = curl_exec($ch))) {
            throw new CurlException(curl_error($ch), curl_errno($ch));
        }

        curl_close($ch);

        $data = json_decode($response);

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @param string $method
     * @param array $data
     * @param array $headers
     *
     * @return array
     */
    private function generateOptions (string $method, array $data, array $headers): array
    {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers
        ];

        switch ($method) {
            case 'POST':
            case 'PUT':
                $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
                $options[CURLOPT_POSTFIELDS] = json_encode($data);
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        if ($this->pathToCertificat) {
            $options[CURLOPT_CAINFO] =  $this->pathToCertificat;
        }
        else {
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = 0;
        }

        return $options;
    }

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return array|stdClass
     * @throws CurlException|ApiException
     */
    public function put (string $endPoint, array $data, array $headers = [])
    {
        return $this->api($endPoint, 'PUT', $data, $headers);
    }

    /**
     * @param string $endPoint
     * @param array $data
     *
     * @param array $headers
     *
     * @return stdClass
     * @throws CurlException|ApiException
     */
    public function patch (string $endPoint, array $data, array $headers = []): stdClass
    {
        return $this->api($endPoint, 'PATCH', $data, $headers);
    }

    /**
     * @param string $endpoint
     *
     * @param array $headers
     *
     * @return stdClass|array
     * @throws ApiException
     * @throws CurlException
     */
    public function get (string $endpoint, array $headers = [])
    {
        return $this->api($endpoint, 'GET', [], $headers);
    }

    /**
     * @param string $endpoint
     *
     * @return stdClass|array
     * @throws CurlException|ApiException
     */
    public function delete (string $endpoint)
    {
        return $this->api($endpoint, 'DELETE');
    }

    /**
     * @param string $url
     *
     * @return Api
     */
    public function setUrl (string $url): Api
    {
        $this->url = $url[-1] === '/' ? $url : $url . '/';
        return $this;
    }

    /**
     * @param string $pathToCertificat path/to/cert.cer
     *
     * @return Api
     * @throws ApiException
     */
    public function setPathToCertificat (string $pathToCertificat): Api
    {
        if (!is_file($pathToCertificat)) {
            throw new ApiException('The certificat path << ' . $pathToCertificat. ' >> does not exist!');
        }

        $this->pathToCertificat = $pathToCertificat;
        return $this;
}
}
