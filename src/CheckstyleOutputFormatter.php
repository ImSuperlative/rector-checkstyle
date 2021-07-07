<?php

declare(strict_types=1);

namespace Rector\Checkstyle;

use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use DOMDocument;

final class CheckstyleOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'checkstyle';

    /*
    protected CheckstyleDOMElementFactory $checkstyleDOMElementFactory;

    public function __construct(CheckstyleDOMElementFactory $checkstyleDOMElementFactory)
    {
        $this->checkstyleDOMElementFactory = $checkstyleDOMElementFactory;
    }
    */

    /**
     * @param \Rector\Core\ValueObject\ProcessResult $processResult
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function report($processResult, $configuration): void
    {
        $domDocument = new DOMDocument('1.0', 'UTF-8');

        $checkstyleDOMElementFactory = new CheckstyleDomElementFactory();
        $domElement = $checkstyleDOMElementFactory->create($domDocument, $processResult);
        //$domElement = $this->checkstyleDOMElementFactory->create($domDocument, $processResult);
        $domDocument->appendChild($domElement);

        // pretty print with spaces
        $domDocument->formatOutput = true;
        echo $domDocument->saveXML();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
