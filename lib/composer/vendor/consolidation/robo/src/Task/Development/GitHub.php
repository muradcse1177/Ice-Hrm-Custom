<?php
namespace Robo\Task\Development;

use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

/**
 * @method \Robo\Task\Development\GitHub repo(string)
 * @method \Robo\Task\Development\GitHub owner(string)
 */
abstract class GitHub extends BaseTask
{
    const GITHUB_URL = 'https://Api.github.com';

    /**
     * @var string
     */
    protected $user = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $repo;

    /**
     * @var string
     */
    protected $owner;

    /**
     * @param string $repo
     *
     * @return $this
     */
    public function repo($repo)
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * @param string $owner
     *
     * @return $this
     */
    public function owner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @param string $uri
     *
     * @return $this
     */
    public function uri($uri)
    {
        list($this->owner, $this->repo) = explode('/', $uri);
        return $this;
    }

    /**
     * @return string
     */
    protected function getUri()
    {
        return $this->owner . '/' . $this->repo;
    }

    /**
     * @param string $user
     *
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param $password
     *
     * @return $this
     */
    public function password($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $uri
     * @param array $params
     * @param string $method
     *
     * @return array
     *
     * @throws \Robo\Exception\TaskException
     */
    protected function sendRequest($uri, $params = [], $method = 'POST')
    {
        if (!$this->owner or !$this->repo) {
            throw new TaskException($this, 'Repo URI is not set');
        }

        $ch = curl_init();
        $url = sprintf('%s/repos/%s/%s', self::GITHUB_URL, $this->getUri(), $uri);
        $this->printTaskInfo($url);
        $this->printTaskInfo('{method} {url}', ['method' => $method, 'url' => $url]);

        if (!empty($this->user)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->user . ':' . $this->password);
        }

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => $method != 'GET',
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => "Robo"
            )
        );

        $output = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = json_decode($output);

        $this->printTaskInfo($output);
        return [$code, $response];
    }
}
