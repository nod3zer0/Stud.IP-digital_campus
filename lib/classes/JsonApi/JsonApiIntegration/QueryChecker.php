<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\ErrorCollection;

class QueryChecker
{
    /**
     * @var bool
     */
    private $allowUnrecognized;

    /**
     * @var array|null
     */
    private $includePaths;

    /**
     * @var array|null
     */
    private $fieldSetTypes;

    /**
     * @var array|null
     */
    private $pagingParameters;

    /**
     * @var array|null
     */
    private $sortParameters;

    /**
     * @var array|null
     */
    private $filteringParameters;

    public function __construct(
        bool $allowUnrecognized = true,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->includePaths = $includePaths;
        $this->allowUnrecognized = $allowUnrecognized;
        $this->fieldSetTypes = $fieldSetTypes;
        $this->sortParameters = $this->flip($sortParameters);
        $this->pagingParameters = $this->flip($pagingParameters);
        $this->filteringParameters = $this->flip($filteringParameters);
    }

    public function checkQuery(QueryParserInterface $queryParser): void
    {
        $errors = new ErrorCollection();

        $this->checkIncludePaths($errors, $queryParser);
        $this->checkFieldSets($errors, $queryParser);
        $this->checkFiltering($errors, $queryParser);
        $this->checkSorting($errors, $queryParser);
        $this->checkPaging($errors, $queryParser);
        $this->checkUnrecognized($errors, $queryParser);

        if ($errors->count()) {
            throw new JsonApiException($errors, JsonApiException::HTTP_CODE_BAD_REQUEST);
        }
    }

    protected function checkIncludePaths(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $withinAllowed = $this->valuesWithinAllowed(
            iterator_to_array($queryParser->getIncludePaths()),
            $this->includePaths
        );
        if (!$withinAllowed) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_INCLUDE,
                'Include paths should contain only allowed ones.'
            );
        }
    }

    protected function checkFieldSets(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $withinAllowed = $this->isFieldsAllowed(iterator_to_array($queryParser->getFields()));
        if (!$withinAllowed) {
            $errors->addQueryParameterError(QueryParser::PARAM_FIELDS, 'Field sets should contain only allowed ones.');
        }
    }

    protected function checkFiltering(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $withinAllowed = $this->keysWithinAllowed(
            iterator_to_array($queryParser->getFilters()),
            $this->filteringParameters
        );
        if (!$withinAllowed) {
            $errors->addQueryParameterError(QueryParser::PARAM_FILTER, 'Filter should contain only allowed values.');
        }
    }

    protected function checkSorting(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $sorts = iterator_to_array($queryParser->getSorts());
        if (null !== $sorts && null !== $this->sortParameters) {
            foreach (array_keys($sorts) as $sortParameter) {
                if (!array_key_exists($sortParameter, $this->sortParameters)) {
                    $errors->addQueryParameterError(
                        QueryParser::PARAM_SORT,
                        sprintf('Sort parameter %s is not allowed.', $sortParameter)
                    );
                }
            }
        }
    }

    protected function checkPaging(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $withinAllowed = $this->keysWithinAllowed(
            iterator_to_array($queryParser->getPagination()),
            $this->pagingParameters
        );
        if (!$withinAllowed) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_PAGE,
                'Page parameter should contain only allowed values.'
            );
        }
    }

    protected function checkUnrecognized(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        if (!$this->allowUnrecognized && !empty($queryParser->getUnrecognizedParameters())) {
            foreach ($queryParser->getUnrecognizedParameters() as $name => $value) {
                $errors->addQueryParameterError($name, 'Unrecognized Parameter.');
            }
        }
    }

    private function keysWithinAllowed(array $toCheck = null, array $allowed = null): bool
    {
        return null === $toCheck || null === $allowed || empty(array_diff_key($toCheck, $allowed));
    }

    private function valuesWithinAllowed(array $toCheck = null, array $allowed = null): bool
    {
        return null === $toCheck || null === $allowed || empty(array_diff($toCheck, $allowed));
    }

    /**
     * @return array|null
     */
    private function flip(array $array = null)
    {
        return $array === null ? null : array_flip($array);
    }

    /**
     * Check input fields against allowed.
     *
     * @param array|null $fields
     */
    private function isFieldsAllowed(array $fields = null): bool
    {
        if ($this->fieldSetTypes === null || $fields === null) {
            return true;
        }

        foreach ($fields as $type => $requestedFields) {
            if (array_key_exists($type, $this->fieldSetTypes) === false) {
                return false;
            }

            $allowedFields = $this->fieldSetTypes[$type];

            // if not all fields are allowed and requested more fields than allowed
            if ($allowedFields !== null && empty(array_diff($requestedFields, $allowedFields)) === false) {
                return false;
            }
        }

        return true;
    }
}
