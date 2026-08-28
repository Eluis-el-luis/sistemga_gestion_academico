<?php

namespace Tests\Unit;

use App\Services\NotaService;
use Tests\TestCase;

class NotaServiceTest extends TestCase
{
    protected NotaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotaService();
    }

    
    public function testcalcularIndicadorLogro_retorna_AA_para_90_a_100(): void
    {
        $this->assertEquals('AA', $this->service->calcularIndicadorLogro(90));
        $this->assertEquals('AA', $this->service->calcularIndicadorLogro(95));
        $this->assertEquals('AA', $this->service->calcularIndicadorLogro(100));
    }

    
    public function testcalcularIndicadorLogro_retorna_AS_para_76_a_89(): void
    {
        $this->assertEquals('AS', $this->service->calcularIndicadorLogro(76));
        $this->assertEquals('AS', $this->service->calcularIndicadorLogro(82));
        $this->assertEquals('AS', $this->service->calcularIndicadorLogro(89));
    }

    
    public function testcalcularIndicadorLogro_retorna_AF_para_60_a_75(): void
    {
        $this->assertEquals('AF', $this->service->calcularIndicadorLogro(60));
        $this->assertEquals('AF', $this->service->calcularIndicadorLogro(67));
        $this->assertEquals('AF', $this->service->calcularIndicadorLogro(75));
    }

    
    public function testcalcularIndicadorLogro_retorna_AI_para_0_a_59(): void
    {
        $this->assertEquals('AI', $this->service->calcularIndicadorLogro(0));
        $this->assertEquals('AI', $this->service->calcularIndicadorLogro(30));
        $this->assertEquals('AI', $this->service->calcularIndicadorLogro(59));
    }

    
    public function testcalcularIndicadorLogro_retorna_null_para_null(): void
    {
        $this->assertNull($this->service->calcularIndicadorLogro(null));
    }

    
    public function testcalcularIndicadorLogro_lanza_excepcion_para_fuera_de_rango(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->calcularIndicadorLogro(-1);
    }

    
    public function testcalcularIndicadorLogro_lanza_excepcion_para_mayor_a_100(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->calcularIndicadorLogro(101);
    }

    
    public function testcalcularNotaSemestral_promedia_dos_cortes(): void
    {
        $this->assertEquals(80, $this->service->calcularNotaSemestral(70, 90));
        $this->assertEquals(85, $this->service->calcularNotaSemestral(85, 85));
        $this->assertEquals(60, $this->service->calcularNotaSemestral(59, 61)); // redondeo
    }

    
    public function testcalcularNotaSemestral_retorna_null_si_falta_corte(): void
    {
        $this->assertNull($this->service->calcularNotaSemestral(80, null));
        $this->assertNull($this->service->calcularNotaSemestral(null, 80));
        $this->assertNull($this->service->calcularNotaSemestral(null, null));
    }

    
    public function testcalcularNotaFinal_promedia_cuatro_cortes(): void
    {
        $this->assertEquals(80, $this->service->calcularNotaFinal(70, 80, 85, 85));
        $this->assertEquals(75, $this->service->calcularNotaFinal(75, 75, 75, 75));
    }

    
    public function testcalcularNotaFinal_retorna_null_si_falta_corte(): void
    {
        $this->assertNull($this->service->calcularNotaFinal(80, 80, 80, null));
        $this->assertNull($this->service->calcularNotaFinal(null, 80, 80, 80));
    }

    
    public function testcalcularPromedioGeneral_ignora_nulos(): void
    {
        $notas = [80, 90, null, 70, 100];
        $this->assertEquals(85.00, $this->service->calcularPromedioGeneral($notas));
    }

    
    public function testcalcularPromedioGeneral_retorna_cero_si_todos_nulos(): void
    {
        $this->assertEquals(0.00, $this->service->calcularPromedioGeneral([null, null, null]));
    }

    
    public function testestaAprobado_retorna_true_desde_60(): void
    {
        $this->assertTrue($this->service->estaAprobado(60));
        $this->assertTrue($this->service->estaAprobado(100));
    }

    
    public function testestaAprobado_retorna_false_menos_de_60(): void
    {
        $this->assertFalse($this->service->estaAprobado(59));
        $this->assertFalse($this->service->estaAprobado(0));
    }
}