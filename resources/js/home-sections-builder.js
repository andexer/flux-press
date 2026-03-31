(function($) {
    'use strict';

    const FluxHomeSectionsBuilder = {
        sections: [],
        sectionTypes: fluxHomeSections.sectionTypes || {},
        
        init() {
            this.cacheDOM();
            this.bindEvents();
            this.loadSections();
        },

        cacheDOM() {
            this.$container = $('#flux-home-sections-container');
            this.$addButton = $('#flux-add-section-btn');
            this.$modal = $('#flux-section-modal');
            this.$form = $('#flux-section-form');
        },

        bindEvents() {
            this.$addButton.on('click', $.proxy(this.openAddModal, this));
            this.$container.on('click', '.flux-section-edit', $.proxy(this.openEditModal, this));
            this.$container.on('click', '.flux-section-delete', $.proxy(this.deleteSection, this));
            this.$container.on('click', '.flux-section-toggle', $.proxy(this.toggleSection, this));
            this.$container.on('click', '.flux-move-up', $.proxy(this.moveSection, this));
            this.$container.on('click', '.flux-move-down', $.proxy(this.moveSection, this));
            this.$container.on('click', '.flux-export-btn', $.proxy(this.exportSections, this));
            this.$container.on('click', '.flux-import-btn', $.proxy(this.importSections, this));
            
            this.$form.on('submit', $.proxy(this.saveSection, this));
            this.$modal.on('click', '.flux-close-modal', $.proxy(this.closeModal, this));
            this.$modal.on('click', '.flux-modal-overlay', $.proxy(this.closeModal, this));
            
            this.$modal.on('change', '#section-type-select', $.proxy(this.onTypeChange, this));
        },

        loadSections() {
            const savedSections = this.$container.data('sections') || [];
            this.sections = savedSections;
            this.renderSections();
        },

        renderSections() {
            if (this.sections.length === 0) {
                this.$container.html('<div class="flux-no-sections">' + 
                    '<p>' + fluxHomeSections.strings.addSection + '</p></div>');
                return;
            }

            let html = '';
            this.sections.forEach((section, index) => {
                const typeInfo = this.sectionTypes[section.type] || { name: section.type, icon: 'cube' };
                html += this.renderSectionCard(section, index, typeInfo);
            });

            this.$container.html(html);
        },

        renderSectionCard(section, index, typeInfo) {
            const enabledClass = section.enabled ? '' : 'flux-section-disabled';
            const toggleIcon = section.enabled ? 'eye' : 'eye-slash';
            
            return `
                <div class="flux-section-card ${enabledClass}" data-section-id="${section.id}">
                    <div class="flux-section-drag">
                        <span class="dashicons dashicons-menu"></span>
                    </div>
                    <div class="flux-section-icon">
                        <span class="dashicons dashicons-${typeInfo.icon}"></span>
                    </div>
                    <div class="flux-section-info">
                        <h4>${typeInfo.name}</h4>
                        <span class="flux-section-id">${section.id}</span>
                    </div>
                    <div class="flux-section-actions">
                        <button type="button" class="button button-secondary flux-move-up" data-index="${index}" title="Move up">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                        </button>
                        <button type="button" class="button button-secondary flux-move-down" data-index="${index}" title="Move down">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button type="button" class="button button-secondary flux-section-toggle" data-id="${section.id}" data-enabled="${section.enabled}" title="Toggle">
                            <span class="dashicons dashicons-${toggleIcon}"></span>
                        </button>
                        <button type="button" class="button button-primary flux-section-edit" data-index="${index}">
                            ${fluxHomeSections.strings.editSection}
                        </button>
                        <button type="button" class="button button-link-delete flux-section-delete" data-id="${section.id}">
                            ${fluxHomeSections.strings.deleteSection}
                        </button>
                    </div>
                </div>
            `;
        },

        openAddModal() {
            this.currentEditIndex = null;
            this.$form[0].reset();
            $('#section-type-select').val('').trigger('change');
            this.$modal.addClass('flux-modal-open');
        },

        openEditModal(e) {
            const index = parseInt($(e.currentTarget).data('index'), 10);
            const section = this.sections[index];
            
            if (!section) return;

            this.currentEditIndex = index;
            $('#section-type-select').val(section.type).trigger('change');
            this.populateFormFields(section.type, section.data || {});
            this.$modal.addClass('flux-modal-open');
        },

        onTypeChange(e) {
            const type = $(e.currentTarget).val();
            this.populateFormFields(type, {});
        },

        populateFormFields(type, data) {
            const container = $('#section-fields-container');
            const typeInfo = this.sectionTypes[type];
            
            if (!typeInfo) {
                container.html('<p>Select a section type</p>');
                return;
            }

            let fieldsHtml = '';
            const fields = typeInfo.fields || [];

            fields.forEach(field => {
                fieldsHtml += this.renderField(type, field, data[field] || '');
            });

            container.html(fieldsHtml);
        },

        renderField(type, field, value) {
            const fieldId = `field_${field}`;
            
            switch (field) {
                case 'title':
                case 'subtitle':
                case 'badge':
                case 'primary_label':
                case 'primary_url':
                case 'button_text':
                case 'button_url':
                case 'email':
                case 'phone':
                case 'video_url':
                case 'image':
                case 'poster':
                    return `
                        <p>
                            <label for="${fieldId}">${this.formatLabel(field)}</label>
                            <input type="text" id="${fieldId}" name="${field}" value="${this.escapeAttr(value)}" class="widefat">
                        </p>
                    `;
                case 'badge_color':
                case 'bg_color':
                    const colorOptions = field === 'badge_color' 
                        ? ['sky', 'lime', 'orange', 'cyan', 'violet', 'rose']
                        : ['bg-slate-900', 'bg-lime-600', 'bg-orange-600', 'bg-cyan-700', 'bg-violet-600', 'bg-rose-600', 'bg-emerald-600'];
                    let options = colorOptions.map(c => `<option value="${c}" ${value === c ? 'selected' : ''}>${c}</option>`).join('');
                    return `
                        <p>
                            <label for="${fieldId}">${this.formatLabel(field)}</label>
                            <select id="${fieldId}" name="${field}" class="widefat">
                                ${options}
                            </select>
                        </p>
                    `;
                case 'items':
                    return this.renderItemsField(type, value);
                case 'html_content':
                    return `
                        <p>
                            <label for="${fieldId}">${this.formatLabel(field)}</label>
                            <textarea id="${fieldId}" name="${field}" class="widefat" rows="6">${this.escapeAttr(value)}</textarea>
                        </p>
                    `;
                case 'limit':
                    return `
                        <p>
                            <label for="${fieldId}">${this.formatLabel(field)}</label>
                            <input type="number" id="${fieldId}" name="${field}" value="${value || 3}" min="1" max="12" class="widefat">
                        </p>
                    `;
                default:
                    return '';
            }
        },

        renderItemsField(type, value) {
            const items = Array.isArray(value) ? value : [];
            let html = `<div class="flux-items-field" data-type="${type}">`;
            
            if (['features', 'stats', 'testimonials', 'team', 'pricing', 'faq'].includes(type)) {
                items.forEach((item, index) => {
                    html += this.renderItemFields(type, item, index);
                });
                html += `<button type="button" class="button flux-add-item">+ Add Item</button>`;
            }
            
            html += '</div>';
            return html;
        },

        renderItemFields(type, item, index) {
            let fields = '';
            
            switch (type) {
                case 'features':
                    fields = `
                        <p><label>Icon</label><input type="text" name="items[${index}][icon]" value="${this.escapeAttr(item.icon)}" class="widefat" placeholder="heroicon name"></p>
                        <p><label>Title</label><input type="text" name="items[${index}][title]" value="${this.escapeAttr(item.title)}" class="widefat"></p>
                        <p><label>Text</label><textarea name="items[${index}][text]" class="widefat">${this.escapeAttr(item.text)}</textarea></p>
                    `;
                    break;
                case 'stats':
                    fields = `
                        <p><label>Value</label><input type="text" name="items[${index}][value]" value="${this.escapeAttr(item.value)}" class="widefat"></p>
                        <p><label>Label</label><input type="text" name="items[${index}][label]" value="${this.escapeAttr(item.label)}" class="widefat"></p>
                    `;
                    break;
                case 'testimonials':
                    fields = `
                        <p><label>Quote</label><textarea name="items[${index}][quote]" class="widefat">${this.escapeAttr(item.quote)}</textarea></p>
                        <p><label>Author</label><input type="text" name="items[${index}][author]" value="${this.escapeAttr(item.author)}" class="widefat"></p>
                        <p><label>Role</label><input type="text" name="items[${index}][role]" value="${this.escapeAttr(item.role)}" class="widefat"></p>
                    `;
                    break;
                case 'pricing':
                    fields = `
                        <p><label>Plan Name</label><input type="text" name="items[${index}][name]" value="${this.escapeAttr(item.name)}" class="widefat"></p>
                        <p><label>Price</label><input type="text" name="items[${index}][price]" value="${this.escapeAttr(item.price)}" class="widefat"></p>
                    `;
                    break;
                case 'faq':
                    fields = `
                        <p><label>Question</label><input type="text" name="items[${index}][question]" value="${this.escapeAttr(item.question)}" class="widefat"></p>
                        <p><label>Answer</label><textarea name="items[${index}][answer]" class="widefat">${this.escapeAttr(item.answer)}</textarea></p>
                    `;
                    break;
            }
            
            return `<div class="flux-item-row">${fields}<button type="button" class="button button-link-delete flux-remove-item">Remove</button></div>`;
        },

        formatLabel(field) {
            return field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        escapeAttr(str) {
            if (!str) return '';
            return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        },

        closeModal() {
            this.$modal.removeClass('flux-modal-open');
        },

        saveSection(e) {
            e.preventDefault();
            
            const type = $('#section-type-select').val();
            if (!type) {
                alert('Please select a section type');
                return;
            }

            const formData = new FormData(this.$form[0]);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('items[')) {
                    const match = key.match(/items\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const idx = parseInt(match[1], 10);
                        const field = match[2];
                        if (!data.items) data.items = [];
                        if (!data.items[idx]) data.items[idx] = {};
                        data.items[idx][field] = value;
                    }
                } else {
                    data[key] = value;
                }
            }

            data.items = data.items ? data.items.filter(i => Object.keys(i).length > 0) : [];

            const ajaxData = {
                action: this.currentEditIndex !== null ? 'flux_home_update_section' : 'flux_home_add_section',
                nonce: fluxHomeSections.nonce,
                type: type,
                data: data
            };

            if (this.currentEditIndex !== null) {
                ajaxData.section_id = this.sections[this.currentEditIndex].id;
            }

            $.post(fluxHomeSections.ajaxUrl, ajaxData)
                .done((response) => {
                    if (response.success) {
                        this.loadSectionsFromServer();
                        this.closeModal();
                    } else {
                        alert(response.data || 'Error saving section');
                    }
                })
                .fail(() => {
                    alert('Ajax error');
                });
        },

        deleteSection(e) {
            if (!confirm(fluxHomeSections.strings.confirmDelete)) return;
            
            const sectionId = $(e.currentTarget).data('id');
            
            $.post(fluxHomeSections.ajaxUrl, {
                action: 'flux_home_delete_section',
                nonce: fluxHomeSections.nonce,
                section_id: sectionId
            }).done((response) => {
                if (response.success) {
                    this.loadSectionsFromServer();
                } else {
                    alert(response.data || 'Error deleting section');
                }
            });
        },

        toggleSection(e) {
            const sectionId = $(e.currentTarget).data('id');
            const enabled = $(e.currentTarget).data('enabled') === true;
            
            $.post(fluxHomeSections.ajaxUrl, {
                action: 'flux_home_toggle_section',
                nonce: fluxHomeSections.nonce,
                section_id: sectionId,
                enabled: !enabled
            }).done((response) => {
                if (response.success) {
                    this.loadSectionsFromServer();
                }
            });
        },

        moveSection(e) {
            const index = parseInt($(e.currentTarget).data('index'), 10);
            const order = this.sections.map(s => s.id);
            
            if ($(e.currentTarget).hasClass('flux-move-up') && index > 0) {
                [order[index], order[index - 1]] = [order[index - 1], order[index]];
            } else if ($(e.currentTarget).hasClass('flux-move-down') && index < order.length - 1) {
                [order[index], order[index + 1]] = [order[index + 1], order[index]];
            } else {
                return;
            }

            $.post(fluxHomeSections.ajaxUrl, {
                action: 'flux_home_reorder_sections',
                nonce: fluxHomeSections.nonce,
                order: order
            }).done((response) => {
                if (response.success) {
                    this.loadSectionsFromServer();
                }
            });
        },

        loadSectionsFromServer() {
            $.post(fluxHomeSections.ajaxUrl, {
                action: 'flux_home_get_sections',
                nonce: fluxHomeSections.nonce
            }).done((response) => {
                if (response.success) {
                    this.sections = response.data || [];
                    this.renderSections();
                }
            });
        },

        exportSections() {
            $.post(fluxHomeSections.ajaxUrl, {
                action: 'flux_home_export_sections',
                nonce: fluxHomeSections.nonce
            }).done((response) => {
                if (response.success) {
                    const blob = new Blob([response.data.json], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    a.click();
                    URL.revokeObjectURL(url);
                } else {
                    alert(response.data || 'Export failed');
                }
            });
        },

        importSections() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (event) => {
                    $.post(fluxHomeSections.ajaxUrl, {
                        action: 'flux_home_import_sections',
                        nonce: fluxHomeSections.nonce,
                        json: event.target.result
                    }).done((response) => {
                        if (response.success) {
                            this.loadSectionsFromServer();
                            alert('Imported ' + response.data.count + ' sections!');
                        } else {
                            alert(response.data || 'Import failed');
                        }
                    });
                };
                reader.readAsText(file);
            };
            
            input.click();
        }
    };

    $(document).ready(function() {
        FluxHomeSectionsBuilder.init();
    });
})(jQuery);