<?php

namespace EM\GameBundle\Response;

use EM\GameBundle\Entity\GameResult;

/**
 * @since 5.0
 */
class GameResultsResponse
{
    const META_INDEX_CURRENT_PAGE = 'currentPage';
    const META_INDEX_TOTAL_PAGES  = 'totalPages';
    /**
     * @var GameResult[]
     */
    private $results = [];
    /**
     * @var string[]
     */
    private $meta = [];

    /**
     * @return GameResult[]
     */
    public function getResults() : array
    {
        return $this->results;
    }

    /**
     * @param GameResult[] $results
     *
     * @return $this
     */
    public function setResults(array $results) : self
    {
        $this->results = $results;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMeta()
    {
        return $this->meta;
    }

    protected function setMeta(string $key, string $value) : self
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function setTotalPages(int $value) : self
    {
        $this->setMeta(self::META_INDEX_TOTAL_PAGES, $value);

        return $this;
    }

    public function setCurrentPage(int $value) : self
    {
        $this->setMeta(self::META_INDEX_CURRENT_PAGE, $value);

        return $this;
    }
}