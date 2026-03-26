<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms', $this->documentData('terms'));
    }

    public function privacy(): View
    {
        return view('legal.privacy', $this->documentData('privacy'));
    }

    public function acceptableUse(): View
    {
        return view('legal.acceptable-use', $this->documentData('acceptable_use'));
    }

    protected function documentData(string $policy): array
    {
        return [
            'companyName' => config('legal.company_name'),
            'companyWebsite' => config('legal.company_website'),
            'contactEmail' => config('legal.contact_email'),
            'jurisdiction' => config('legal.jurisdiction'),
            'serviceRegion' => config('legal.global.service_region'),
            'governingLaw' => config('legal.global.governing_law'),
            'disputeResolution' => config('legal.global.dispute_resolution'),
            'consumerProtectionNotice' => config('legal.global.consumer_protection_notice'),
            'restrictedCountriesNotice' => config('legal.global.restricted_countries_notice'),
            'pricesIncludeTaxes' => config('legal.global.prices_include_taxes'),
            'billingGracePeriodDays' => config('legal.billing.grace_period_days'),
            'billingTerminationDays' => config('legal.billing.termination_days'),
            'billingReactivationHours' => config('legal.billing.reactivation_hours'),
            'billingDataRetentionDays' => config('legal.billing.data_retention_days'),
            'ipOwnerName' => config('legal.intellectual_property.owner_name'),
            'ipOwnerWebsite' => config('legal.intellectual_property.owner_website'),
            'ipInfringementContactEmail' => config('legal.intellectual_property.infringement_contact_email'),
            'ipTakedownResponseDays' => config('legal.intellectual_property.takedown_response_days'),
            'policyVersion' => config("legal.{$policy}.version"),
            'effectiveDate' => config("legal.{$policy}.effective_date"),
        ];
    }
}
