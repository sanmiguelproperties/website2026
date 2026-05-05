<?php

return [
    'guards' => ['web', 'api'],

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
        'properties.view.all',
        'properties.create',
        'properties.edit',
        'properties.edit.own',
        'properties.approve',
        'properties.publish',
        'properties.assign',
        'properties.status.update',
        'properties.delete',
        'properties.delete.own',
        'properties.restore',
        'properties.export',

        'leads.view',
        'leads.view.own',
        'leads.view.all',
        'leads.create',
        'leads.edit',
        'leads.edit.own',
        'leads.assign',
        'leads.status.update',
        'leads.delete',
        'leads.delete.own',
        'leads.restore',
        'leads.export',

        'clients.view',
        'clients.view.own',
        'clients.view.all',
        'clients.create',
        'clients.edit',
        'clients.edit.own',
        'clients.delete',
        'clients.restore',
        'clients.export',

        'crm.notes.view.own',
        'crm.notes.view.all',
        'crm.notes.create',
        'crm.notes.edit',
        'crm.notes.edit.own',
        'crm.notes.delete',
        'crm.notes.delete.own',

        'closings.view',
        'closings.view.own',
        'closings.view.all',
        'closings.create',
        'closings.edit',
        'closings.edit.own',
        'closings.approve',
        'closings.delete',
        'closings.export',

        'documents.view',
        'documents.view.own',
        'documents.view.all',
        'documents.create',
        'documents.edit',
        'documents.edit.own',
        'documents.delete',
        'documents.restore',
        'documents.export',

        'reports.view',
        'reports.view.global',
        'reports.export',

        'commissions.view',
        'commissions.view.own',
        'commissions.view.all',
        'commissions.edit',
        'commissions.approve',
        'commissions.export',

        'pipelines.view',
        'pipelines.view.own',
        'pipelines.view.all',
        'pipelines.edit',
        'pipelines.edit.own',

        'calendar.view',
        'calendar.view.own',
        'calendar.view.all',
        'calendar.edit',
        'calendar.edit.own',

        'cms.view',
        'cms.manage',

        'catalogs.view',
        'catalogs.manage',

        'settings.view',
        'settings.manage',

        'integrations.view',
        'integrations.logs.view',
        'integrations.sync',
        'integrations.config.view',
        'integrations.config.edit',
        'integrations.manage',

        'marketing.view',
        'sensitive.view',
        'financial.view',
        'records.delete.critical',
    ],

    'roles' => [
        'super-admin' => [
            'label' => 'Administrador',
            'permissions' => '*',
        ],

        'manager' => [
            'label' => 'Manager',
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
                'properties.delete',
                'properties.restore',
                'properties.export',

                'leads.view',
                'leads.view.all',
                'leads.create',
                'leads.edit',
                'leads.assign',
                'leads.status.update',
                'leads.delete',
                'leads.restore',
                'leads.export',

                'clients.view',
                'clients.view.all',
                'clients.create',
                'clients.edit',
                'clients.delete',
                'clients.restore',

                'crm.notes.view.all',
                'crm.notes.create',

                'closings.view',
                'closings.view.all',
                'closings.create',
                'closings.edit',
                'closings.approve',
                'closings.delete',
                'closings.export',

                'documents.view',
                'documents.view.all',
                'documents.create',
                'documents.edit',
                'documents.delete',
                'documents.restore',

                'reports.view',
                'reports.view.global',

                'commissions.view',
                'commissions.view.all',

                'pipelines.view',
                'pipelines.view.all',
                'pipelines.edit',

                'calendar.view',
                'calendar.view.all',
                'calendar.edit',

                'cms.view',
                'catalogs.view',
                'integrations.view',
                'integrations.logs.view',
                'integrations.sync',
                'marketing.view',
                'sensitive.view',
                'financial.view',
            ],
        ],

        'assistant' => [
            'label' => 'Asistente',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.all',
                'properties.create',
                'properties.edit',
                'properties.status.update',
                'properties.delete',
                'properties.restore',
                'properties.export',

                'documents.view',
                'documents.view.all',
                'documents.create',
                'documents.edit',
                'documents.delete',
                'documents.restore',

                'cms.view',
                'cms.manage',
                'catalogs.view',
                'catalogs.manage',

                'integrations.view',
                'integrations.logs.view',
                'integrations.sync',
            ],
        ],

        'agent' => [
            'label' => 'Agente',
            'permissions' => [
                'dashboard.view',

                'properties.view',
                'properties.view.all',
                'properties.create',
                'properties.edit.own',
                'properties.delete.own',
                'properties.restore',

                'leads.view',
                'leads.view.own',
                'leads.create',
                'leads.edit.own',
                'leads.status.update',
                'leads.delete.own',
                'leads.restore',

                'clients.view',
                'clients.view.own',
                'clients.create',
                'clients.edit.own',

                'crm.notes.view.own',
                'crm.notes.create',
                'crm.notes.edit.own',
                'crm.notes.delete.own',

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

                'commissions.view.own',
                'marketing.view',
            ],
        ],
    ],
];
