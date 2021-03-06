<?php

declare(strict_types=1);

namespace Rector\Checkstyle;

use DOMDocument;
use DOMElement;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Core\ValueObject\Reporting\FileDiff;

/**
 * Inspiration in @see \Symfony\Component\Console\Descriptor\XmlDescriptor
 */
final class CheckstyleDomElementFactory
{
    /**
     * @var string
     */
    private const CHECKSTYLE = 'checkstyle';

    /**
     * @var string
     */
    private const FILE = 'file';

    /**
     * @var string
     */
    private const ERROR = 'error';

    public function create(DOMDocument $domDocument, ProcessResult $processResult): DOMElement
    {
        $domElement = $domDocument->createElement(self::CHECKSTYLE);

        foreach ($processResult->getFileDiffs() as $fileDiff) {
            $fileDOMElement = $this->createFileDOMElement($domDocument, $fileDiff);
            $domElement->appendChild($fileDOMElement);
        }

        $nonFileErrorDOMElement = $this->createNonFileErrorDOMElements($domDocument, $processResult);
        if ($nonFileErrorDOMElement !== null) {
            $domElement->appendChild($nonFileErrorDOMElement);
        }

        return $domElement;
    }

    private function createFileDOMElement(DOMDocument $domDocument, FileDiff $fileDiff): DOMElement
    {
        $domElement = $domDocument->createElement(self::FILE);
        $domElement->setAttribute('name', $this->escapeForXml($fileDiff->getRelativeFilePath()));

        foreach ($fileDiff->getRectorChanges() as $rectorWithFileAndLineChange) {
            $errorDOMElement = $this->createErrorDOMElement($rectorWithFileAndLineChange, $domDocument);
            $domElement->appendChild($errorDOMElement);
        }

        return $domElement;
    }

    private function createNonFileErrorDOMElements(
        DOMDocument $domDocument,
        ProcessResult $processResult
    ): ?DOMElement {
        if ($processResult->getErrors() === []) {
            return null;
        }

        $domElement = $domDocument->createElement(self::FILE);

        foreach ($processResult->getErrors() as $rectorError) {
            $errorDOMElement = $domDocument->createElement(self::ERROR);
            $errorDOMElement->setAttribute('severity', self::ERROR);
            $errorDOMElement->setAttribute('message', $this->escapeForXml($rectorError->getMessage()));

            $domElement->appendChild($errorDOMElement);
        }

        return $domElement;
    }

    private function escapeForXml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_COMPAT);
    }

    private function createErrorDOMElement(
        RectorWithLineChange $rectorWithFileAndLineChange,
        DOMDocument $domDocument
    ): DOMElement {
        $domElement = $domDocument->createElement(self::ERROR);

        $domElement->setAttribute('line', $this->escapeForXml((string) $rectorWithFileAndLineChange->getLine()));
        $domElement->setAttribute('column', '1');
        $domElement->setAttribute('severity', self::ERROR);

        $message = '(Reported by: ' . $rectorWithFileAndLineChange->getRectorClass() . ')';
        $domElement->setAttribute('message', $this->escapeForXml($message));

        return $domElement;
    }
}
