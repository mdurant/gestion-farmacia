<?php

namespace App\Enums;

enum ReportType: string
{
    case Kardex = 'kardex';
    case ResidentConsumption = 'consumo-residentes';
    case Valuation = 'valorizacion';
    case MonthlyWaste = 'mermas-mensuales';
    case PurchaseProjection = 'proyeccion-compra';
    case Charts = 'graficos';

    public function label(): string
    {
        return match ($this) {
            self::Kardex => 'Kardex de movimientos',
            self::ResidentConsumption => 'Consumo por residente',
            self::Valuation => 'Valorización de inventario',
            self::MonthlyWaste => 'Mermas mensuales',
            self::PurchaseProjection => 'Proyección de compra',
            self::Charts => 'Gráficos analíticos',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Kardex => 'Trazabilidad completa de entradas, salidas y traslados.',
            self::ResidentConsumption => 'Administraciones agrupadas por residente y fármaco.',
            self::Valuation => 'Valor del stock actual por bodega y fármaco.',
            self::MonthlyWaste => 'Resumen mensual de mermas en cantidad y valor.',
            self::PurchaseProjection => 'Fármacos bajo mínimo con cantidad sugerida de reposición.',
            self::Charts => 'Inventario, consumo, proveedores y auditoría de pérdidas en gráficos.',
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Kardex => 'reports.kardex',
            self::ResidentConsumption => 'reports.resident-consumption',
            self::Valuation => 'reports.valuation',
            self::MonthlyWaste => 'reports.monthly-waste',
            self::PurchaseProjection => 'reports.purchase-projection',
            self::Charts => 'reports.charts',
        };
    }

    public function isExecutive(): bool
    {
        return in_array($this, [
            self::Valuation,
            self::MonthlyWaste,
            self::PurchaseProjection,
            self::Charts,
        ], true);
    }

    public static function fromSlug(string $slug): self
    {
        return self::from($slug);
    }
}
