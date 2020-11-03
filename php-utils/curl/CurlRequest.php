<?php


class CurlRequest
{
    private $ch;

    private $clearOption;

    private $options = [
        CURLOPT_HEADER                => 0,
        CURLOPT_FORBID_REUSE          => 1,
        CURLOPT_FRESH_CONNECT         => 1,
        CURLOPT_SSL_VERIFYHOST        => 0,
        CURLOPT_SSL_VERIFYPEER        => 0,
        CURLOPT_RETURNTRANSFER        => 1,
        CURLOPT_FOLLOWLOCATION        => 1,
        CURLOPT_DNS_USE_GLOBAL_CACHE  => 0,
    ];

    public static $browserHeaders = [
        'X-Requested-With'          => 'XMLHttpRequest',
        'Content-Type'              => 'application/x-www-form-urlencoded; charset=UTF-8',
        'Cache-control'             => 'max-age=0',
        'Upgrade-insecure-requests' => 1,
        'User-agent'                => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.97 Safari/537.36',
        'Uec-fetch-user'            => '?1',
        'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
        'U-compress'                => null,
        'Uec-fetch-site'            => 'none',
        'Uec-fetch-mode'            => 'navigate',
        'Accept-encoding'           => 'deflate, br',
        'Accept-language'           => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    ];


    public function connect($url, $params = [], $clearOption = true)
    {
        $this->clearOption = $clearOption;
        $this->ch = curl_init($url);
        $this->setOption(CURLOPT_URL,
            $url . (strpos($url, '?') === FALSE ? '?' : '') . http_build_query($params)
        );
        return $this;
    }

    public function get()
    {
        return $this->execute();
    }

    public function post($data = null)
    {
        $this->setOptions([CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $data]);
        return $this->execute();
    }

    public function put($data)
    {
        $this->setOptions([CURLOPT_PUT, 1, CURLOPT_POSTFIELDS => $data]);
        return $this->execute();
    }

    public function patch($data = null)
    {
        $this->setOptions([CURLOPT_CUSTOMREQUEST => 'PATCH', CURLOPT_POSTFIELDS => $data]);
        return $this->execute();
    }

    public function delete($data = null)
    {
        $this->setOptions([CURLOPT_CUSTOMREQUEST => 'DELETE', CURLOPT_POSTFIELDS => $data]);
        return $this->execute();
    }

    public function postFile($file)
    {
        if (file_exists($file)) {
            $curlFile = curl_file_create($file, mime_content_type($file), basename($file));
        }
        return $this->post($curlFile);
    }

    public function postFiles(array $files)
    {
        $curlFiles = [];
        foreach ($files as $file) {
            if (file_exists($file)) {
                $curlFiles[] = curl_file_create($file, mime_content_type($file), basename($file));
            }
        }
        return $this->post($curlFiles);
    }

    public function putFile($file)
    {
        $fp = fopen($file, 'r');
        $this->setOptions([
            CURLOPT_PUT => 1,
            CURLOPT_UPLOAD => 1,
            CURLOPT_INFILE => $fp,
            CURLOPT_INFILESIZE => filesize($file),
        ]);
        return $this->execute();
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options += $this->options + $options;
        return $this;
    }

    public function addHeader($name, $value)
    {
        $this->setOption(CURLOPT_HTTPHEADER, ["$name: $value"]);
        return $this;
    }

    public function addHeaders(array $headers)
    {
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this;
    }

    public function execute()
    {
        curl_setopt_array($this->ch, $this->options);
        $result = curl_exec($this->ch);
        if ($result === false) {
            trigger_error(curl_error($this->ch));
        }
        $this->close();
        return $result;
    }

    public function close()
    {
        if ($this->clearOption) {
            curl_reset($this->ch);
        }
        curl_close($this->ch);
    }

    public function login($username, $password, $cookiePath = null)
    {
        $this->clearOption = false;
        if (!$cookiePath) {
            $cookiePath = dirname(__FILE__) . '/../cookie.txt';
        }
        $this->setOptions([
            CURLOPT_COOKIEJAR   => 1,
            CURLOPT_COOKIEFILE  => $cookiePath,
            CURLINFO_HEADER_OUT => $cookiePath,
        ]);
        $postData = "login=$username&password=$password";
        return $this->post($postData);
    }
}
