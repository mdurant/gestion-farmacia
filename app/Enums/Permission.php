<?php

namespace App\Enums;

enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case InventoryView = 'inventory.view';
    case InventoryMove = 'inventory.move';
    case InventoryWaste = 'inventory.waste';
    case PharmaciesManage = 'pharmacies.manage';
    case ResidentsView = 'residents.view';
    case ResidentsManage = 'residents.manage';
    case ReportsInternal = 'reports.internal';
    case ReportsExecutive = 'reports.executive';
    case UsersManage = 'users.manage';
    case ControlledDrugAuthorize = 'drugs.controlled.authorize';
    case SupportAccess = 'support.access';

    public function label(): string
    {
        return match ($this) {
            self::DashboardView => 'Ver dashboard',
            self::InventoryView => 'Ver inventario',
            self::InventoryMove => 'Registrar movimientos',
            self::InventoryWaste => 'Registrar mermas',
            self::PharmaciesManage => 'Gestionar bodegas',
            self::ResidentsView => 'Ver residentes',
            self::ResidentsManage => 'Gestionar residentes',
            self::ReportsInternal => 'Reportes internos',
            self::ReportsExecutive => 'Reportes gerenciales',
            self::UsersManage => 'Gestionar usuarios',
            self::ControlledDrugAuthorize => 'Autorizar fármacos controlados',
            self::SupportAccess => 'Acceder a soporte',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DashboardView => 'Panel central con KPIs, alertas y movimientos recientes.',
            self::InventoryView => 'Consultar stock, fármacos, lotes y kardex.',
            self::InventoryMove => 'Registrar entradas, traslados y administraciones.',
            self::InventoryWaste => 'Registrar salidas por merma o vencimiento.',
            self::PharmaciesManage => 'Crear y editar bodegas, centros de costo y traslados.',
            self::ResidentsView => 'Consultar fichas e historial de administraciones.',
            self::ResidentsManage => 'Alta, edición y baja lógica de residentes.',
            self::ReportsInternal => 'Kardex y consumo por residente.',
            self::ReportsExecutive => 'Valorización, mermas mensuales y proyección de compra.',
            self::UsersManage => 'Crear, editar y desactivar usuarios del sistema.',
            self::ControlledDrugAuthorize => 'Aprobar movimientos de fármacos controlados o narcóticos.',
            self::SupportAccess => 'Acceder a la página de ayuda y contacto.',
        };
    }

    public function group(): string
    {
        return match ($this) {
            self::DashboardView, self::SupportAccess => 'general',
            self::InventoryView, self::InventoryMove, self::InventoryWaste, self::ControlledDrugAuthorize => 'inventory',
            self::PharmaciesManage => 'pharmacies',
            self::ResidentsView, self::ResidentsManage => 'residents',
            self::ReportsInternal, self::ReportsExecutive => 'reports',
            self::UsersManage => 'administration',
        };
    }

    public function groupLabel(): string
    {
        return match ($this->group()) {
            'general' => 'General',
            'inventory' => 'Inventario y farmacia',
            'pharmacies' => 'Bodegas',
            'residents' => 'Residentes',
            'reports' => 'Reportes',
            'administration' => 'Administración',
        };
    }

    /** @return list<string> */
    public static function groupOrder(): array
    {
        return ['general', 'inventory', 'pharmacies', 'residents', 'reports', 'administration'];
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
