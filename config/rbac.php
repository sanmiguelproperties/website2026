<?php

return [
    'guards' => ['web', 'api'],

    /*
     * Permission grammar:
     * - {module}.view / create / edit / approve / delete / export
     * - {module}.view.own, {module}.view.team, {module}.view.all
     * - {module}.edit.own, {module}.edit.assigned
     */
    'permissions' => [
        'dashboard.view',

        'users.view',
        'users.view.all',
        'users.create',
        'users.edit',
        'users.deactivate',
        'users.delete',
        'users.export',

        'rbac.view',
        'rbac.manage',

        'properties.view',
        'properties.view.own',
        'properties.view.team',
        'properties.view.all',
        'properties.create',
        'properties.edit',
        'properties.edit.own',
        'properties.approve',
        'properties.publish',
        'properties.assign',
        'properties.status.update',
        'properties.delete',
        'properties.export',

        'leads.view',
        'leads.view.own',
        'leads.view.team',
        'leads.view.all',
        'leads.create',
        'leads.edit',
        'leads.edit.own',
        'leads.edit.assigned',
        'leads.approve',
        'leads.assign',
        'leads.status.update',
        'leads.delete',
        'leads.export',

        'clients.view',
        'clients.view.own',
        'clients.view.team',
        'clients.view.all',
        'clients.create',
        'clients.edit',
        'clients.edit.own',
        'clients.edit.assigned',
        'clients.approve',
        'clients.delete',
        'clients.export',

        'closings.view',
        'closings.view.own',
        'closings.view.team',
        'closings.view.all',
        'closings.create',
        'closings.edit',
        'closings.edit.own',
        'closings.edit.assigned',
        'closings.approve',
        'closings.delete',
        'closings.export',

        'documents.view',
        'documents.view.own',
        'documents.view.team',
        'documents.view.all',
        'documents.create',
        'documents.edit',
        'documents.edit.own',
        'documents.edit.assigned',
        'documents.approve',
        'documents.delete',
        'documents.export',

        'reports.view',
        'reports.view.operational',
        'reports.view.commercial',
        'reports.view.global',
        'reports.export',
        'reports.export.operational',
        'reports.export.commercial',

        'commissions.view',
        'commissions.view.own',
        'commissions.view.all',
        'commissions.edit',
        'commissions.approve',
        'commissions.export',

        'pipelines.view',
        'pipelines.view.own',
        'pipelines.view.team',
        'pipelines.view.all',
        'pipelines.edit',
        'pipelines.edit.own',

        'calendar.view',
        'calendar.view.own',
        'calendar.view.assigned',
        'calendar.edit',
        'calendar.edit.own',
        'calendar.edit.assigned',

        'marketing.view',

        'catalogs.view',
        'catalogs.manage',

        'settings.view',
        'settings.manage',

        'integrations.view',
        'integrations.manage',

        'sensitive.view',
        'financial.view',
        'records.delete.critical',
    ],

    'roles' => [
        'super-admin' => [
            'label' => 'Super Admin',
            'permissions' => '*',
        ],

        'broker' => [
            'label' => 'Direccion / Broker',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.all',
                'properties.create',
                'properties.edit',
                'properties.approve',
                'properties.publish',
                'properties.assign',
                'properties.status.update',
                'properties.export',

                'leads.view',
                'leads.view.all',
                'leads.edit',
                'leads.approve',
                'leads.assign',
                'leads.status.update',
                'leads.export',

                'clients.view',
                'clients.view.all',
                'clients.edit',
                'clients.approve',
                'clients.export',

                'closings.view',
                'closings.view.all',
                'closings.edit',
                'closings.approve',
                'closings.export',

                'documents.view',
                'documents.view.all',
                'documents.create',
                'documents.edit',
                'documents.approve',
                'documents.export',

                'reports.view',
                'reports.view.commercial',
                'reports.view.global',
                'reports.export.commercial',

                'commissions.view',
                'commissions.view.all',
                'commissions.export',

                'pipelines.view',
                'pipelines.view.all',
                'pipelines.edit',

                'calendar.view',
                'calendar.edit',

                'catalogs.view',
                'catalogs.manage',

                'sensitive.view',
                'financial.view',
            ],
        ],

        'operations-admin' => [
            'label' => 'Admin / Operaciones',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.all',
                'properties.create',
                'properties.edit',
                'properties.status.update',
                'properties.assign',
                'properties.export',

                'leads.view',
                'leads.view.all',
                'leads.create',
                'leads.edit',
                'leads.assign',
                'leads.status.update',

                'clients.view',
                'clients.view.all',
                'clients.create',
                'clients.edit',

                'closings.view',
                'closings.view.all',
                'closings.create',
                'closings.edit',

                'documents.view',
                'documents.view.all',
                'documents.create',
                'documents.edit',
                'documents.approve',

                'reports.view',
                'reports.view.operational',
                'reports.export.operational',

                'pipelines.view',
                'pipelines.view.all',
                'pipelines.edit',

                'calendar.view',
                'calendar.edit',

                'catalogs.view',
                'catalogs.manage',
            ],
        ],

        'agent' => [
            'label' => 'Agente',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.own',
                'properties.create',
                'properties.edit.own',

                'leads.view',
                'leads.view.own',
                'leads.create',
                'leads.edit.own',
                'leads.status.update',

                'clients.view',
                'clients.view.own',
                'clients.create',
                'clients.edit.own',

                'closings.view',
                'closings.view.own',
                'closings.edit.own',

                'documents.view',
                'documents.view.own',
                'documents.create',
                'documents.edit.own',

                'pipelines.view',
                'pipelines.view.own',
                'pipelines.edit.own',

                'calendar.view',
                'calendar.view.own',
                'calendar.edit.own',

                'marketing.view',
                'commissions.view.own',
            ],
        ],

        'assistant' => [
            'label' => 'Asistente / Soporte',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.own',
                'properties.edit.own',

                'leads.view',
                'leads.view.own',
                'leads.edit.assigned',

                'clients.view',
                'clients.view.own',
                'clients.edit.assigned',

                'closings.view',
                'closings.view.own',
                'closings.edit.assigned',

                'documents.view',
                'documents.view.own',
                'documents.create',
                'documents.edit.assigned',

                'calendar.view',
                'calendar.view.assigned',
                'calendar.edit.assigned',

                'pipelines.view',
                'pipelines.view.own',
            ],
        ],
    ],
];
