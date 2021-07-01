<?php

/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 20/04/2016
 * Time: 2:32 PM
 */

namespace Freshdesk;

use Freshdesk\Exceptions\AccessDeniedException;
use Freshdesk\Exceptions\ApiException;
use Freshdesk\Exceptions\AuthenticationException;
use Freshdesk\Exceptions\ConflictingStateException;
use Freshdesk\Exceptions\RateLimitExceededException;
use Freshdesk\Exceptions\UnsupportedContentTypeException;
use Freshdesk\Resources\Agent;
use Freshdesk\Resources\Article;
use Freshdesk\Resources\BusinessHour;
use Freshdesk\Resources\Comment;
use Freshdesk\Resources\Company;
use Freshdesk\Resources\Contact;
use Freshdesk\Resources\Conversation;
use Freshdesk\Resources\Discussion;
use Freshdesk\Resources\EmailConfig;
use Freshdesk\Resources\Folder;
use Freshdesk\Resources\Forum;
use Freshdesk\Resources\Group;
use Freshdesk\Resources\Product;
use Freshdesk\Resources\SLAPolicy;
use Freshdesk\Resources\Solution;
use Freshdesk\Resources\Ticket;
use Freshdesk\Resources\TimeEntry;
use Freshdesk\Resources\Topic;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class for interacting with the Freshdesk Api
 *
 * This is the only class that should be instantiated directly. All API resources are available
 * via the relevant public properties
 *
 * @package Api
 * @author Matthew Clarkson <mpclarkson@gmail.com>
 */
class Api
{
    /**
     * Agent resources
     *
     * @api
     * @var Agent
     */
    public $agents;

    /**
     * Company resources
     *
     * @api
     * @var Company
     */
    public $companies;

    /**
     * Contact resources
     *
     * @api
     * @var Contact
     */
    public $contacts;

    /**
     * Group resources
     *
     * @api
     * @var Group
     */
    public $groups;

    /**
     * Ticket resources
     *
     * @api
     * @var Ticket
     */
    public $tickets;

    /**
     * TimeEntry resources
     *
     * @api
     * @var TimeEntry
     */
    public $timeEntries;

    /**
     * Conversation resources
     *
     * @api
     * @var Conversation
     */
    public $conversations;

    /**
     * Discussion resources
     *
     * @api
     * @var Discussion
     */
    public $discussions;

    /**
     * Forum resources
     *
     * @api
     * @var Forum
     */
    public $forums;

    /**
     * Topic resources
     *
     * @api
     * @var Topic
     */
    public $topics;

    /**
     * Comment resources
     *
     * @api
     * @var Comment
     */
    public $comments;

    //Admin

    /**
     * Email Config resources
     *
     * @api
     * @var EmailConfig
     */
    public $emailConfigs;

    /**
     * Access Product resources
     *
     * @api
     * @var Product
     */
    public $products;

    /**
     * Business Hours resources
     *
     * @api
     * @var BusinessHour
     */
    public $businessHours;

    /**
     * SLA Policy resources
     *
     * @api
     * @var SLAPolicy
     */
    public $slaPolicies;

    /**
     * Solution resources
     *
     * @api
     * @var Solution
     */
    public $solutions;

    /**
     * Folder resources
     *
     * @api
     * @var Folder
     */
    public $folders;

    /**
     * Folder resources
     *
     * @api
     * @var Article
     */
    public $articles;

    /**
     * @internal
     * @var Client
     */
    protected $client;

    /**
     * @internal
     * @var string
     */
    private $baseUrl;

     /**
     * @api
     * @var string
     */
    public $headers;


    /**
     * @api
     * @var string
     */
    public $result;

    /**
     * Constructs a new api instance
     *
     * @api
     * @param string $apiKey
     * @param string $domain
     * @param array $options
     * @throws Exceptions\InvalidConfigurationException
     */
    public function __construct($apiKey, $domain, $options = [])
    {
        $this->validateConstructorArgs($apiKey, $domain);

        $this->baseUrl = sprintf('https://%s.freshdesk.com/api/v2', $domain);

        $this->client = new Client(array_merge($options, ['auth' => [$apiKey, 'X']]));

        $this->setupResources();
    }

    /**
     * Internal method for handling requests
     *
     * @internal
     * @param $method
     * @param $endpoint
     * @param array|null $data
     * @param array|null $query
     * @return mixed|null
     * @throws ApiException
     * @throws ConflictingStateException
     * @throws RateLimitExceededException
     * @throws UnsupportedContentTypeException
     */
    public function request($method, $endpoint, array $data = null, array $query = null)
    {
        $options = ['json' => $data];

        if (isset($query)) {
            $options['query'] = $query;
        }

        $url = $this->baseUrl . $endpoint;

        return $this->performRequest($method, $url, $options);
    }

    /**
     * Internal method for handling requests multipart
     *
     * @internal
     * @param $method
     * @param $endpoint
     * @param array|null $data
     * @param array|null $query
     * @return mixed|null
     * @throws ApiException
     * @throws ConflictingStateException
     * @throws RateLimitExceededException
     * @throws UnsupportedContentTypeException
     */
    public function requestMultipart($method, $endpoint, array $data = null, array $query = null)
    {
        $options = ['multipart' => $data];

        if (isset($query)) {
            $options['query'] = $query;
        }

        $url = $this->baseUrl . $endpoint;

        return $this->performRequest($method, $url, $options);
    }

    /**
     * Performs the request
     *
     * @internal
     *
     * @param $method
     * @param $url
     * @param $options
     * @return mixed|null
     * @throws AccessDeniedException
     * @throws ApiException
     * @throws AuthenticationException
     * @throws ConflictingStateException
     */
    private function performRequest($method, $url, $options)
    {

        try {
            $request = $this->client->request($method, $url, $options);

            $this->headers = $request->getHeaders();
            $this->results = json_decode($request->getBody(), true);
            return $this;
        } catch (RequestException $e) {
            throw ApiException::create($e);
        }
    }

    /**
     * @param $apiKey
     * @param $domain
     * @throws Exceptions\InvalidConfigurationException
     * @internal
     *
     */
    private function validateConstructorArgs($apiKey, $domain)
    {
        if (!isset($apiKey)) {
            throw new Exceptions\InvalidConfigurationException("API key is empty.");
        }

        if (!isset($domain)) {
            throw new Exceptions\InvalidConfigurationException("Domain is empty.");
        }
    }

    /**
     * @internal
     */
    private function setupResources()
    {
        //People
        $this->agents    = new Agent($this);
        $this->companies = new Company($this);
        $this->contacts  = new Contact($this);
        $this->groups    = new Group($this);

        //Tickets
        $this->tickets       = new Ticket($this);
        $this->timeEntries   = new TimeEntry($this);
        $this->conversations = new Conversation($this);

        //Discussions
        $this->discussions = new Discussion($this);
        $this->forums      = new Forum($this);
        $this->topics      = new Topic($this);
        $this->comments    = new Comment($this);

        //Solution
        $this->solutions = new Solution($this);
        $this->folders   = new Folder($this);
        $this->articles  = new Article($this);

        //Admin
        $this->products      = new Product($this);
        $this->emailConfigs  = new EmailConfig($this);
        $this->slaPolicies   = new SLAPolicy($this);
        $this->businessHours = new BusinessHour($this);
    }
}
