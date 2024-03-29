<?php

namespace JsonApi\JsonApiIntegration;

/**
 * Diese Klasse bündelt lediglich die Logik, um aus dem Triple [Gesamtanzahl,
 * Offset, Limit], die neuen Triple für die nächste, vorherige, erste
 * oder letzte Seite zu generieren.
 *
 * @internal
 */
class Paginator
{
    /** @var int|null */
    private $total;

    /** @var int */
    private $offset;

    /** @var int */
    private $limit;

    /**
     * @param int|null $total
     * @param int      $offset
     * @param int      $limit
     */
    public function __construct($total, $offset, $limit)
    {
        $this->total = $total;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function getFirstPageOffsetAndLimit(): array
    {
        if (0 === $this->limit) {
            return [0, 0];
        }

        return [0, $this->limit];
    }

    public function getLastPageOffsetAndLimit(): ?array
    {
        if (!isset($this->total)) {
            return null;
        }

        if ($this->limit === 0) {
            return [0, 0];
        }

        $last = max(0, floor($this->total / $this->limit - 1) * $this->limit);

        return [$last, $this->limit];
    }

    public function getPrevPageOffsetAndLimit(): ?array
    {
        if ($this->limit === 0) {
            return null;
        }

        if ($this->offset - $this->limit < 0) {
            return null;
        }

        return [max(0, $this->offset - $this->limit), $this->limit];
    }

    public function getNextPageOffsetAndLimit(): ?array
    {
        if ($this->limit === 0) {
            return null;
        }

        if (isset($this->total) && $this->total <= $this->offset + $this->limit) {
            return null;
        }

        return [$this->offset + $this->limit, $this->limit];
    }
}
