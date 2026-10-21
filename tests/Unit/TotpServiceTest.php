<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_it_validates_rfc_totp_code(): void
    {
        $service = new TotpService;

        $this->assertSame(1, $service->verify('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', '287082', 59));
    }

    public function test_it_rejects_malformed_and_incorrect_codes(): void
    {
        $service = new TotpService;

        $this->assertNull($service->verify('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', '12345', 59));
        $this->assertNull($service->verify('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', '000000', 59));
        $this->assertNull($service->verify('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', '287082', 121));
    }
}
