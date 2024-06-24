jQuery(document).ready(function($) {
    // Add row functionality
    $('#add-row').on('click', function() {
        var rowCount = $('#dynamic-table tbody tr').length + 1;
        var colCount = $('#dynamic-table thead tr th').length - 1; // Minus one to exclude the checkbox column
        var newRow = '<tr><th class="w-36 bg-gray-50 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" scope="row"><input type="checkbox" class="row-checkbox"> Row ' + rowCount + '</th>';
        for (var i = 0; i < colCount; i++) {
            newRow += '<td contenteditable="true" class="contenteditable px-3 py-4 whitespace-nowrap text-sm text-gray-500">Editable Cell ' +
                '<button type="button" class="add-media">' +
                '<svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">' +
                '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>' +
                '</svg></button></td>';
        }
        newRow += '</tr>';
        $('#dynamic-table tbody').append(newRow);
        initializeMediaButtons();
    });

    // Add column functionality
    $('#add-column').on('click', function() {
        var colCount = $('#dynamic-table thead tr th').length;
        var newColIndex = colCount;
        $('#dynamic-table thead tr').append('<th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white-500 uppercase tracking-wider min-w-[300px] max-w-[300px]"><input type="checkbox" class="column-checkbox"> Column ' + newColIndex + '</th>');
        $('#dynamic-table tbody tr').each(function() {
            $(this).append('<td contenteditable="true" class="contenteditable px-3 py-4 whitespace-nowrap text-sm text-white-500 min-w-[300px] max-w-[300px]">Editable Cell ' +
                '<button type="button" class="add-media">' +
                '<svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">' +
                '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>' +
                '</svg></button></td>');
        });
        initializeMediaButtons();
    });

    // Export CSV functionality
    $('#export-csv').on('click', function() {
        var csv = [];
        $('#dynamic-table tr').each(function() {
            var row = [];
            $(this).find('th, td').each(function() {
                row.push($(this).text().replace(/[\n\r]+|[\s]{2,}/g, ' ').trim());
            });
            csv.push(row.join(','));
        });

        var csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "table_data.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Save table functionality
    $('#table-creator-form').on('submit', function(e) {
        e.preventDefault();
        console.log("Save Table button clicked");
    
        var tableData = [];
        $('#dynamic-table tbody tr').each(function() {
            var rowData = [];
            $(this).find('td').each(function() {
                var cellContent = $(this).html();
                rowData.push(cellContent);
            });
            tableData.push(rowData);
        });
    
        var isHeaderRowChecked = $('#is-header-row').is(':checked') ? 1 : 0;
    
        var data = {
            'action': 'save_table',
            'title': $('#table-title').val(),
            'table': tableData,
            'table-id': $('#table-id').val(),
            'is-header-row': isHeaderRowChecked
        };
    
        console.log("Sending AJAX request to save table", data);
    
        $.post(ajax_object.ajax_url, data, function(response) {
            console.log("Save Table AJAX response", response);
            if (response.success) {
                alert('Table saved!');
                window.location.href = window.location.href; // Reload the page to see changes
            } else {
                alert('An error occurred while saving the table.');
            }
        }).fail(function(xhr, status, error) {
            alert('AJAX request failed: ' + status + ', ' + error);
            console.log(xhr.responseText);
        });
    });
    

    // Delete table functionality
    $(document).on('click', '.delete-table', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this table?')) {
            return;
        }

        var tableId = $(this).data('id');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_table',
                id: tableId
            },
            success: function(response) {
                if (response.success) {
                    alert('Table deleted successfully!');
                    location.reload(); // Reload the page to update the table list
                } else {
                    alert('An error occurred while deleting the table.');
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX request failed: ' + status + ', ' + error);
            }
        });
    });

    // Delete selected rows
    $('#delete-rows').on('click', function() {
        $('#dynamic-table tbody .row-checkbox:checked').closest('tr').remove();
    });

    // Delete selected columns
    $('#delete-columns').on('click', function() {
        $('#dynamic-table thead .column-checkbox:checked').each(function() {
            var colIndex = $(this).closest('th').index();
            $('#dynamic-table tr').each(function() {
                $(this).find('th, td').eq(colIndex).remove();
            });
        });
    });

    // Select all rows
    $('#select-all-rows').on('click', function() {
        var isChecked = $(this).data('checked');
        $('#dynamic-table .row-checkbox').prop('checked', !isChecked);
        $(this).data('checked', !isChecked);
    });

    // Select all columns
    $('#select-all-columns').on('click', function() {
        var isChecked = $(this).data('checked');
        $('#dynamic-table .column-checkbox').prop('checked', !isChecked);
        $(this).data('checked', !isChecked);
    });

    // Add media button functionality
    $(document).on('click', '.add-media', function() {
        var button = $(this);
        var cell = button.closest('td');

        var customUploader = wp.media({
            title: 'Select Media',
            button: {
                text: 'Add Media'
            },
            multiple: false
        }).on('select', function() {
            var attachment = customUploader.state().get('selection').first().toJSON();
            var imgHTML = '<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px;">';
            cell.html(imgHTML + '<button type="button" class="add-media">' +
                '<svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">' +
                '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>' +
                '</svg></button>');
        }).open();
    });

    // Reinitialize the media button after page load and any dynamic changes
    function initializeMediaButtons() {
        $('.add-media').off('click').on('click', function() {
            var button = $(this);
            var cell = button.closest('td');

            var customUploader = wp.media({
                title: 'Select Media',
                button: {
                    text: 'Add Media'
                },
                multiple: false
            }).on('select', function() {
                var attachment = customUploader.state().get('selection').first().toJSON();
                var imgHTML = '<img src="' + attachment.url + '" style="max-width: 100px; max-height: 100px;">';
                cell.html(imgHTML + '<button type="button" class="add-media">' +
                    '<svg class="w-8 h-5 mb-0 custom-svg custom-svg-hover" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">' +
                    '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>' +
                    '</svg></button>');
            }).open();
        });
    }

    // Initialize media buttons on page load
    initializeMediaButtons();

    // Reinitialize media buttons after any changes to the table
    $('#add-row, #add-column').on('click', function() {
        initializeMediaButtons();
    });
});
