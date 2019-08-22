<?php

namespace Freshdesk\Resources;

use Freshdesk\Resources\Traits\CategoryTrait;
use Freshdesk\Resources\Traits\DeleteTrait;
use Freshdesk\Resources\Traits\UpdateTrait;
use Freshdesk\Resources\Traits\ViewTrait;

/**
 * Forum category resource
 *
 * This provides access to the knowledge base category resources
 *
 * @package Api\Resources
 */
class Folder extends AbstractResource
{

    use CategoryTrait, ViewTrait, UpdateTrait, DeleteTrait;

    /**
     * The resource endpoint
     *
     * @internal
     * @var string
     *
     */
    protected $endpoint = '/solutions/folders';

    /**
     * The resource endpoint
     *
     * @var string
     * @internal
     */
    protected $categoryEndpoint = '/solutions/categories';

    /**
     * Creates the category endpoint (for creating folders)
     *
     * @param integer $id
     * @return string
     * @internal
     */
    private function categoryEndpoint($id = null)
    {
        return $id === null ? $this->categoryEndpoint : $this->categoryEndpoint . '/' . $id;
    }

    /**
     *
     * Create a folders for a category.
     *
     * @api
     * @param int $id The category Id
     * @param array $data
     * @return array|null
     * @throws \Freshdesk\Exceptions\AccessDeniedException
     * @throws \Freshdesk\Exceptions\ApiException
     * @throws \Freshdesk\Exceptions\AuthenticationException
     * @throws \Freshdesk\Exceptions\ConflictingStateException
     * @throws \Freshdesk\Exceptions\NotFoundException
     * @throws \Freshdesk\Exceptions\RateLimitExceededException
     * @throws \Freshdesk\Exceptions\UnsupportedContentTypeException
     * @throws \Freshdesk\Exceptions\MethodNotAllowedException
     * @throws \Freshdesk\Exceptions\UnsupportedAcceptHeaderException
     * @throws \Freshdesk\Exceptions\ValidationException
     */
    public function create($id, array $data)
    {
        return $this->api()->request('POST', $this->categoryEndpoint($id), $data);
    }

}
