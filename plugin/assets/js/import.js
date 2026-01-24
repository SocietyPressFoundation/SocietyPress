/**
 * SocietyPress Import JavaScript
 *
 * Handles the multi-step CSV import process:
 * 1. File upload
 * 2. Field mapping
 * 3. Preview
 * 4. Import execution
 *
 * @package SocietyPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Import Module
     */
    var SocietyPressImport = {

        // Current state
        currentFile: null,
        headers: [],
        sampleRows: [],
        mapping: {},
        totalRows: 0,
        problemRows: [],      // Rows that need user review
        importOptions: {},    // Options used during import (for committing fixes)

        /**
         * Initialize the import module.
         */
        init: function() {
            this.bindEvents();
            this.initDragDrop();
        },

        /**
         * Bind event handlers.
         */
        bindEvents: function() {
            var self = this;

            // File input change
            $('#csv-file').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('.upload-text').text(fileName);
                    $('#upload-btn').prop('disabled', false);
                }
            });

            // Upload form submit
            $('#csv-upload-form').on('submit', function(e) {
                e.preventDefault();
                self.uploadFile();
            });

            // Navigation buttons
            $('#back-to-upload').on('click', function() {
                self.showStep('upload');
            });

            $('#back-to-mapping').on('click', function() {
                self.showStep('mapping');
            });

            // Preview button
            $('#preview-btn').on('click', function() {
                self.previewImport();
            });

            // Run import button
            $('#run-import-btn').on('click', function() {
                if (confirm(societypressImport.strings.confirmImport)) {
                    self.runImport();
                }
            });

            // Save mapping button
            $('#save-mapping-btn').on('click', function() {
                self.saveMapping();
            });

            // Load mapping button
            $('#load-mapping-btn').on('click', function() {
                self.loadSavedMapping();
            });

            // New import button
            $('#new-import-btn').on('click', function() {
                self.reset();
            });

            // Skip all problem rows button
            $('#skip-problem-rows-btn').on('click', function() {
                if (confirm(societypressImport.strings.confirmSkipAll)) {
                    self.showStep('results');
                }
            });

            // Commit fixed rows button
            $('#commit-fixes-btn').on('click', function() {
                self.commitFixedRows();
            });
        },

        /**
         * Initialize drag and drop for file upload.
         */
        initDragDrop: function() {
            var $uploadArea = $('#upload-area');
            var self = this;

            $uploadArea.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });

            $uploadArea.on('dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });

            $uploadArea.on('drop', function(e) {
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    $('#csv-file')[0].files = files;
                    $('#csv-file').trigger('change');
                }
            });
        },

        /**
         * Upload the CSV file.
         */
        uploadFile: function() {
            var self = this;
            var $btn = $('#upload-btn');
            var formData = new FormData();

            formData.append('action', 'societypress_upload_csv');
            formData.append('nonce', societypressImport.nonce);
            formData.append('csv_file', $('#csv-file')[0].files[0]);

            $btn.prop('disabled', true).text(societypressImport.strings.uploading);

            $.ajax({
                url: societypressImport.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.currentFile = response.data.file;
                        self.headers = response.data.headers;
                        self.sampleRows = response.data.sample_rows;
                        self.totalRows = response.data.total_rows;

                        // Apply suggested or saved mapping
                        self.mapping = response.data.suggested_mapping || {};

                        if (response.data.saved_mapping && Object.keys(response.data.saved_mapping).length) {
                            $('#load-mapping-btn').show();
                        }

                        self.buildMappingTable();
                        self.showStep('mapping');
                    } else {
                        alert(response.data.message || societypressImport.strings.error);
                    }
                },
                error: function() {
                    alert(societypressImport.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Upload and Continue');
                }
            });
        },

        /**
         * Build the field mapping table.
         */
        buildMappingTable: function() {
            var self = this;
            var $tbody = $('#mapping-body');
            var fields = societypressImport.destinationFields;

            $tbody.empty();

            // Build select options HTML
            var optionsHtml = '<option value="">— Do not import —</option>';

            $.each(fields, function(group, groupFields) {
                var groupLabel = group.charAt(0).toUpperCase() + group.slice(1);
                optionsHtml += '<optgroup label="' + groupLabel + '">';

                $.each(groupFields, function(key, label) {
                    optionsHtml += '<option value="' + key + '">' + label + '</option>';
                });

                optionsHtml += '</optgroup>';
            });

            // Build rows
            $.each(self.headers, function(index, header) {
                var sampleData = self.getSampleData(index);
                var selectedValue = self.mapping[index] || '';

                var $row = $('<tr>');
                $row.append('<td><strong>' + self.escapeHtml(header) + '</strong></td>');
                $row.append('<td class="sample-data">' + self.escapeHtml(sampleData) + '</td>');

                var $select = $('<select>')
                    .attr('name', 'mapping[' + index + ']')
                    .attr('data-index', index)
                    .html(optionsHtml)
                    .val(selectedValue)
                    .on('change', function() {
                        self.mapping[$(this).data('index')] = $(this).val();
                    });

                $row.append($('<td>').append($select));
                $tbody.append($row);
            });

            // Update file info
            $('#file-info').html(
                '<strong>' + self.totalRows + '</strong> rows found in file'
            );
        },

        /**
         * Get sample data for a column.
         */
        getSampleData: function(columnIndex) {
            var samples = [];

            for (var i = 0; i < Math.min(3, this.sampleRows.length); i++) {
                if (this.sampleRows[i][columnIndex]) {
                    samples.push(this.sampleRows[i][columnIndex]);
                }
            }

            return samples.join(' | ');
        },

        /**
         * Preview the import.
         */
        previewImport: function() {
            var self = this;
            var $btn = $('#preview-btn');

            // Validate mapping
            var hasMapping = false;
            $.each(self.mapping, function(key, value) {
                if (value) {
                    hasMapping = true;
                    return false;
                }
            });

            if (!hasMapping) {
                alert(societypressImport.strings.noFieldsSelected);
                return;
            }

            $btn.prop('disabled', true).text(societypressImport.strings.processing);

            $.ajax({
                url: societypressImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_preview_import',
                    nonce: societypressImport.nonce,
                    file: self.currentFile,
                    mapping: self.mapping
                },
                success: function(response) {
                    if (response.success) {
                        self.displayPreview(response.data.preview);
                        self.showStep('preview');
                    } else {
                        alert(response.data.message || societypressImport.strings.error);
                    }
                },
                error: function() {
                    alert(societypressImport.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Preview Import');
                }
            });
        },

        /**
         * Display the import preview.
         */
        displayPreview: function(previewData) {
            var self = this;
            var $container = $('#preview-container');
            var fields = this.flattenFields(societypressImport.destinationFields);

            // Build preview table
            var html = '<table class="widefat striped">';
            html += '<thead><tr>';

            // Get mapped fields for headers
            var mappedFields = [];
            $.each(self.mapping, function(index, field) {
                if (field) {
                    mappedFields.push({
                        index: index,
                        field: field,
                        label: fields[field] || field
                    });
                }
            });

            $.each(mappedFields, function(i, item) {
                html += '<th>' + self.escapeHtml(item.label) + '</th>';
            });

            html += '</tr></thead><tbody>';

            // Add preview rows
            $.each(previewData, function(i, row) {
                html += '<tr>';
                $.each(mappedFields, function(j, item) {
                    var value = row[item.field] || '';
                    html += '<td>' + self.escapeHtml(value) + '</td>';
                });
                html += '</tr>';
            });

            html += '</tbody></table>';

            html += '<p class="description">Showing first ' + previewData.length + ' of ' + self.totalRows + ' rows</p>';

            $container.html(html);
        },

        /**
         * Run the actual import.
         */
        runImport: function() {
            var self = this;
            var $btn = $('#run-import-btn');

            var options = {
                skip_duplicates: $('input[name="duplicate_handling"]:checked').val() === 'skip',
                update_existing: $('input[name="duplicate_handling"]:checked').val() === 'update',
                default_tier: $('#default-tier').val()
            };

            // Save options for potential commit of fixed rows later
            self.importOptions = options;

            $btn.prop('disabled', true).text(societypressImport.strings.importing);

            $.ajax({
                url: societypressImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_run_import',
                    nonce: societypressImport.nonce,
                    file: self.currentFile,
                    mapping: self.mapping,
                    options: options
                },
                success: function(response) {
                    if (response.success) {
                        // Check if there are problem rows that need review
                        if (response.data.problem_rows && response.data.problem_rows.length > 0) {
                            self.problemRows = response.data.problem_rows;
                            self.displayReviewStep(response.data);
                            self.showStep('review');
                        } else {
                            self.displayResults(response.data);
                            self.showStep('results');
                        }
                    } else {
                        alert(response.data.message || societypressImport.strings.error);
                        $btn.prop('disabled', false).text('Run Import');
                    }
                },
                error: function() {
                    alert(societypressImport.strings.error);
                    $btn.prop('disabled', false).text('Run Import');
                }
            });
        },

        /**
         * Display import results.
         */
        displayResults: function(results) {
            var html = '<div class="societypress-import-results">';

            html += '<div class="result-stat result-imported">';
            html += '<span class="result-number">' + results.imported + '</span>';
            html += '<span class="result-label">Imported</span>';
            html += '</div>';

            if (results.updated > 0) {
                html += '<div class="result-stat result-updated">';
                html += '<span class="result-number">' + results.updated + '</span>';
                html += '<span class="result-label">Updated</span>';
                html += '</div>';
            }

            if (results.skipped > 0) {
                html += '<div class="result-stat result-skipped">';
                html += '<span class="result-number">' + results.skipped + '</span>';
                html += '<span class="result-label">Skipped</span>';
                html += '</div>';
            }

            html += '</div>';

            // Show errors if any
            if (results.errors && results.errors.length > 0) {
                html += '<div class="societypress-import-errors">';
                html += '<h3>Errors</h3>';
                html += '<ul>';
                $.each(results.errors, function(i, error) {
                    html += '<li>' + error + '</li>';
                });
                html += '</ul>';
                html += '</div>';
            }

            $('#results-container').html(html);
        },

        /**
         * Display the review step for problem rows.
         */
        displayReviewStep: function(results) {
            var self = this;
            var $summary = $('#review-summary');
            var $container = $('#problem-rows-container');

            // Show summary
            var summaryHtml = '<p>';
            summaryHtml += '<strong>' + results.imported + '</strong> ' + societypressImport.strings.importedSuccess;
            if (results.updated > 0) {
                summaryHtml += ', <strong>' + results.updated + '</strong> updated';
            }
            summaryHtml += '. <strong>' + results.problem_rows.length + '</strong> ' + societypressImport.strings.needReview + '.';
            summaryHtml += '</p>';
            $summary.html(summaryHtml);

            // Build problem rows UI
            var html = '';

            $.each(results.problem_rows, function(index, row) {
                html += '<div class="problem-row" data-index="' + index + '">';
                html += '<div class="problem-row-header">';
                html += '<span class="row-number">Row ' + row.row_num + '</span>';
                html += '<span class="row-issue"><strong>' + societypressImport.strings.issue + ':</strong> ' + self.escapeHtml(row.issue) + '</span>';
                html += '<button type="button" class="button button-small discard-row-btn">' + societypressImport.strings.discard + '</button>';
                html += '</div>';
                html += '<div class="problem-row-fields">';

                // Show editable fields for key data
                html += self.buildEditableField(index, 'first_name', 'First Name', row.data.first_name || '');
                html += self.buildEditableField(index, 'last_name', 'Last Name', row.data.last_name || '');
                html += self.buildEditableField(index, 'primary_email', 'Email', row.data.primary_email || '');
                html += self.buildEditableField(index, 'organization', 'Organization', row.data.organization || '');
                html += self.buildTierSelect(index, row.data.membership_tier || '');

                html += '</div>';
                html += '</div>';
            });

            $container.html(html);

            // Bind discard button events
            $container.find('.discard-row-btn').on('click', function() {
                var $row = $(this).closest('.problem-row');
                var idx = $row.data('index');

                if ($row.hasClass('discarded')) {
                    // Restore
                    $row.removeClass('discarded');
                    $(this).text(societypressImport.strings.discard);
                    self.problemRows[idx].discard = false;
                } else {
                    // Discard
                    $row.addClass('discarded');
                    $(this).text(societypressImport.strings.restore);
                    self.problemRows[idx].discard = true;
                }
            });

            // Bind input change events to update problemRows data
            $container.find('input, select').on('change', function() {
                var $input = $(this);
                var idx = $input.closest('.problem-row').data('index');
                var field = $input.data('field');
                self.problemRows[idx].data[field] = $input.val();
            });
        },

        /**
         * Build an editable field for problem row review.
         */
        buildEditableField: function(rowIndex, fieldKey, label, value) {
            var html = '<div class="field-group">';
            html += '<label>' + this.escapeHtml(label) + '</label>';
            html += '<input type="text" data-field="' + fieldKey + '" value="' + this.escapeHtml(value) + '"';
            if (fieldKey === 'first_name' || fieldKey === 'last_name') {
                html += ' class="required-field"';
                if (!value) {
                    html += ' style="border-color: #d63638;"';
                }
            }
            html += '>';
            html += '</div>';
            return html;
        },

        /**
         * Build a tier select dropdown for problem row review.
         */
        buildTierSelect: function(rowIndex, currentTier) {
            var html = '<div class="field-group">';
            html += '<label>Membership Tier</label>';
            html += '<select data-field="membership_tier">';
            html += '<option value="">— Select Tier —</option>';

            if (societypressImport.tiers) {
                $.each(societypressImport.tiers, function(i, tier) {
                    var selected = (tier.name.toLowerCase() === currentTier.toLowerCase()) ? ' selected' : '';
                    html += '<option value="' + tier.name + '"' + selected + '>' + tier.name + '</option>';
                });
            }

            html += '</select>';
            html += '</div>';
            return html;
        },

        /**
         * Commit the fixed problem rows.
         */
        commitFixedRows: function() {
            var self = this;
            var $btn = $('#commit-fixes-btn');

            $btn.prop('disabled', true).text(societypressImport.strings.committing);

            $.ajax({
                url: societypressImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_commit_problem_rows',
                    nonce: societypressImport.nonce,
                    rows: self.problemRows,
                    options: self.importOptions
                },
                success: function(response) {
                    if (response.success) {
                        // Merge results with original import results
                        var finalResults = {
                            imported: response.data.imported,
                            updated: 0,
                            skipped: response.data.skipped,
                            errors: response.data.errors || []
                        };
                        self.displayResults(finalResults);
                        self.showStep('results');
                    } else {
                        alert(response.data.message || societypressImport.strings.error);
                        $btn.prop('disabled', false).text('Import Fixed Rows');
                    }
                },
                error: function() {
                    alert(societypressImport.strings.error);
                    $btn.prop('disabled', false).text('Import Fixed Rows');
                }
            });
        },

        /**
         * Save the current field mapping.
         */
        saveMapping: function() {
            var self = this;

            $.ajax({
                url: societypressImport.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'societypress_save_mapping',
                    nonce: societypressImport.nonce,
                    mapping: self.mapping
                },
                success: function(response) {
                    if (response.success) {
                        alert(societypressImport.strings.mappingSaved);
                    } else {
                        alert(response.data.message || societypressImport.strings.error);
                    }
                },
                error: function() {
                    alert(societypressImport.strings.error);
                }
            });
        },

        /**
         * Load saved mapping.
         */
        loadSavedMapping: function() {
            // This would need to be implemented to load mapping by header names
            // For now, just rebuild the table with existing mapping
            this.buildMappingTable();
        },

        /**
         * Show a specific step.
         */
        showStep: function(step) {
            $('.import-step').hide();
            $('#step-' + step).show();
        },

        /**
         * Reset the import form.
         */
        reset: function() {
            this.currentFile = null;
            this.headers = [];
            this.sampleRows = [];
            this.mapping = {};
            this.totalRows = 0;
            this.problemRows = [];
            this.importOptions = {};

            $('#csv-file').val('');
            $('.upload-text').text('Click to select CSV file or drag and drop');
            $('#upload-btn').prop('disabled', true);
            $('#mapping-body').empty();
            $('#preview-container').empty();
            $('#problem-rows-container').empty();
            $('#review-summary').empty();
            $('#results-container').empty();

            this.showStep('upload');
        },

        /**
         * Flatten the grouped fields object.
         */
        flattenFields: function(grouped) {
            var flat = {};
            $.each(grouped, function(group, fields) {
                $.each(fields, function(key, label) {
                    flat[key] = label;
                });
            });
            return flat;
        },

        /**
         * Escape HTML entities.
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * Initialize on document ready.
     */
    $(document).ready(function() {
        if ($('.societypress-import').length) {
            SocietyPressImport.init();
        }
    });

})(jQuery);
