# CMS I18N Edit Guide (ES/EN)

Ruta de administracion:
- `Admin > CMS > Pages` (`/admin/cms/pages`)

Objetivo:
- Editar primero llaves globales de UI (header/footer/layout).
- Luego cerrar textos por vista publica.

## 1) Llaves globales (editar en TODAS las paginas publicas)
Estas llaves existen en los grupos `*-texts-auto` de cada slug publico.  
Para consistencia visual, aplica el mismo texto en todas:

- `header_nav_home`
- `header_nav_properties`
- `header_nav_offices`
- `header_nav_agents`
- `header_nav_about`
- `header_nav_contact`
- `header_cta_dashboard`
- `header_cta_login`
- `header_mobile_menu`
- `header_switch_to_en`
- `header_switch_to_es`
- `footer_site_tagline`
- `footer_newsletter_title`
- `footer_newsletter_text`
- `footer_newsletter_placeholder`
- `footer_newsletter_button`
- `footer_quick_links`
- `footer_services`
- `footer_contact`
- `footer_phone`
- `footer_email`
- `footer_address`
- `footer_hours`
- `footer_whatsapp`
- `footer_about`
- `footer_properties`
- `footer_privacy`
- `footer_terms`
- `footer_copyright`
- `footer_office_hours`
- `layout_back_to_top_aria`

## 2) Prioridad por pagina

### Home (`slug: home`)
Prioridad alta:
- `home_page_title`
- `home_hero_loading`
- `hero_title_line1`
- `hero_title_highlight`
- `hero_subtitle`
- `hero_search_placeholder`
- `hero_search_button`
- `services_title`
- `properties_title`
- `home_contact_title`
- `home_contact_form_submit`

### About (`slug: about`)
Prioridad alta:
- `about_hero_cta_primary`
- `about_hero_cta_secondary`
- `about_cta_button_primary`
- `about_cta_button_secondary`
- `about_team_member_cta`

### Contact (`slug: contact`)
Prioridad alta:
- `contact_info_title`
- `contact_info_subtitle`
- `contact_form_title`
- `contact_form_subtitle`
- `contact_form_submit`
- `contact_form_sending`
- `contact_faq_title`
- `contact_faq_whatsapp_cta`

### Properties (`slug: properties`)
Prioridad alta:
- `page_title_prefix`
- `page_title_highlight`
- `page_subtitle`
- `search_placeholder`
- `sort_label`
- `empty_title`
- `empty_subtitle`
- `pagination_previous`
- `pagination_next`

### Property Detail (`slug: property-detail`)
Prioridad alta:
- `cta_back`
- `cta_share`
- `price_hint`
- `advisor_note`
- `i18n_section_description`
- `i18n_section_features`
- `i18n_section_location`
- `i18n_error_title`

### MLS Offices (`slug: mls-offices`)
Prioridad alta:
- `page_title_prefix`
- `page_title_highlight`
- `page_subtitle`
- `search_placeholder`
- `sort_label`
- `empty_title`
- `empty_subtitle`

### MLS Office Detail (`slug: mls-office-detail`)
Prioridad alta:
- `properties_title`
- `properties_subtitle`
- `agents_title`
- `search_placeholder`

### MLS Agents (`slug: mls-agents`)
Prioridad alta:
- `page_title_prefix`
- `page_title_highlight`
- `page_subtitle`
- `search_placeholder`
- `agency_filter_label`
- `agency_filter_placeholder`
- `empty_title`
- `empty_subtitle`

### MLS Agent Detail (`slug: mls-agent-detail`)
Prioridad alta:
- `properties_title`
- `properties_subtitle`
- `search_placeholder`
- `i18n_label_bio`
- `i18n_label_contact`

## 3) Slugs con grupo auto creado
Estado confirmado:
- `home` (`home-texts-auto`)
- `about` (`about-texts-auto`)
- `contact` (`contact-texts-auto`)
- `properties` (`properties-texts-auto`)
- `property-detail` (`property-detail-texts-auto`)
- `mls-offices` (`mls-offices-texts-auto`)
- `mls-office-detail` (`mls-office-detail-texts-auto`)
- `mls-agents` (`mls-agents-texts-auto`)
- `mls-agent-detail` (`mls-agent-detail-texts-auto`)

## 4) Recomendacion operativa
- Completa primero globales (seccion 1).
- Luego edita pagina por pagina solo prioridad alta.
- Publica y valida frontend con switch ES/EN (`/idioma/en` y `/idioma/es`).
