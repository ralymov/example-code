<?php

namespace App\Models\Import\Site;

use App\Helpers\GuzzleHelper;
use App\Models\Import\ImportApi;
use App\Models\Import\ImportConverter;
use App\Models\Import\ImportSource;
use App\Models\Import\ImportValidator;

class SiteSource extends ImportSource {

    public function getData(string $filename = null): ?iterable {
        $body = [
            'token' => $this->source->import_token,
            'last_request_date' => $this->source->last->format('Y-m-d H:i:s')
        ];
        $url = $this->source->url . '?' . http_build_query($body);
        return GuzzleHelper::makeRequest($url, $body);
    }

    /**
     * @return ImportValidator
     */
    public function getValidator(): ImportValidator {
        return new SiteValidator();
    }

    /**
     * @return ImportConverter
     */
    public function getConverter(): ImportConverter {
        return new SiteConverter($this->source);
    }

}
