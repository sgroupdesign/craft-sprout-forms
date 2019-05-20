<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\Integration;
use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

/**
 * Route our request to Craft or a third-party endpoint
 *
 * Payload forwarding is only available on front-end requests. Any
 * data saved to the database after a forwarded request is editable
 * in Craft as normal, but will not trigger any further calls to
 * the third-party endpoint.
 */
class CustomEndpoint extends Integration
{
    /**
     * The URL to use when submitting the Form payload
     *
     * @var string
     */
    public $submitAction;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Custom Endpoint');
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        $this->prepareFieldMapping();

        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/customendpoint/settings',
            [
                'integration' => $this
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function submit(): bool
    {
        if ($this->submitAction == '' || Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        return $this->forwardEntry();
    }

    /**
     * @inheritDoc
     */
    public function resolveFieldMapping(): array
    {
        $fields = [];
        $entry = $this->entry;

        if ($this->fieldMapping) {
            foreach ($this->fieldMapping as $fieldMap) {
                if (isset($entry->{$fieldMap['sourceFormField']}) && $fieldMap['targetIntegrationField']) {
                    $fields[$fieldMap['targetIntegrationField']] = $entry->{$fieldMap['sourceFormField']};
                }
            }
        }

        return $fields;
    }

    /**
     * @return bool
     */
    private function forwardEntry(): bool
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $message = $entry->formName.' submit action is an invalid URL: '.$endpoint;
            $this->logResponse(false, $message);
            Craft::error($message, __METHOD__);

            return false;
        }

        $client = new Client();

        try {
            Craft::info($fields, __METHOD__);

            $response = $client->post($endpoint, [
                RequestOptions::JSON => $fields
            ]);

            $this->logResponse(true, $response->getBody()->getContents());
            Craft::info($response->getBody()->getContents(), __METHOD__);
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            $this->logResponse(false, $e->getMessage());
            return false;
        }

        return true;
    }
}

