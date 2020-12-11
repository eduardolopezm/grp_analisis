/*The MIT License (MIT)
 __
 Copyright (c) 2014 https://github.com/kayalshri/
 
 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:
 
 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.
 
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.*/

(function ($) {
    $.fn.extend({
        tableExport: function (options) {
            var defaults = {
                separator: ',',
                csvEnclosure: '"',
                csvSeparator: ',',
                csvUseBOM: true,
                ignoreColumn: [],
                displayTableName: false,
                excelstyles: [], // e.g. ['border-bottom', 'border-top', 'border-left', 'border-right']
                fileName: 'tableExport',
                ignoreRow: [],
                jsonScope: 'all', // head, data, all
                pdfFontSize: 9,
                pdfLeftMargin: 20,
                jspdf: {orientation: 'p',
                    unit: 'pt',
                    format: 'a4', // jspdf page format or 'bestfit' for autmatic paper format selection
                    margins: {left: 20, right: 10, top: 10, bottom: 10},
                    autotable: {styles: {cellPadding: 2,
                            rowHeight: 12,
                            fontSize: 8,
                            fillColor: 255, // color value or 'inherit' to use css background-color from html table
                            textColor: 50, // color value or 'inherit' to use css color from html table
                            fontStyle: 'normal', // normal, bold, italic, bolditalic or 'inherit' to use css font-weight and fonst-style from html table
                            overflow: 'ellipsize', // visible, hidden, ellipsize or linebreak
                            halign: 'left', // left, center, right
                            valign: 'middle'       // top, middle, bottom
                        },
                        headerStyles: {fillColor: [52, 73, 94],
                            textColor: 255,
                            fontStyle: 'bold',
                            halign: 'center'
                        },
                        alternateRowStyles: {fillColor: 245
                        },
                        tableExport: {onAfterAutotable: null,
                            onBeforeAutotable: null,
                            onTable: null
                        }
                    }
                },
                numbers: {html: {decimalMark: '.',
                        thousandsSeparator: ','
                    },
                    output: {decimalMark: '.',
                        thousandsSeparator: ','
                    }
                },
                onCellData: null,
                onCellHtmlData: null,
                outputMode: 'file', // 'file', 'string' or 'base64'
                tbodySelector: 'tr',
                theadSelector: 'tr',
                type: 'csv', // 'csv', 'txt', 'sql', 'json', 'xml', 'excel', 'doc', 'png' or 'pdf'
                worksheetName: 'xlsWorksheetName'
            };

            var options = $.extend(defaults, options);
            var el = this;
            var DownloadEvt = null;
            var $hrows = [];
            var $rows = [];
            var rowIndex = 0;
            var rowspans = [];
            var trData = '';
            var colNames = [];
            var FONT_ROW_RATIO = 1.15;

            $.extend(true, defaults, options);

            colNames = GetColumnNames(el);



            if (defaults.type == 'csv' || defaults.type == 'txt') {

                // Header
                var tdData = "";
                $(el).find('thead').find('tr').each(function () {
                    tdData += "\n";
                    $(this).filter(':visible').find('th').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                tdData += '"' + parseString($(this)) + '"' + defaults.separator;
                            }
                        }

                    });
                    tdData = $.trim(tdData);
                    tdData = $.trim(tdData).substring(0, tdData.length - 1);
                });

                // Row vs Column
                $(el).find('tbody').find('tr').each(function () {
                    tdData += "\n";
                    $(this).filter(':visible').find('td').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                tdData += '"' + parseString($(this)) + '"' + defaults.separator;
                            }
                        }
                    });
                    //tdData = $.trim(tdData);
                    tdData = $.trim(tdData).substring(0, tdData.length - 1);
                });

                //output
                if (defaults.consoleLog == 'true') {
                    console.log(tdData);
                }
                var base64data = "base64," + $.base64.encode(tdData);
                window.open('data:application/' + defaults.type + ';filename=exportData;' + base64data);
            } else if (defaults.type == 'sql') {

                // Header
                var tdData = "INSERT INTO `" + defaults.tableName + "` (";
                $(el).find('thead').find('tr').each(function () {

                    $(this).filter(':visible').find('th').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                tdData += '`' + parseString($(this)) + '`,';
                            }
                        }

                    });
                    tdData = $.trim(tdData);
                    tdData = $.trim(tdData).substring(0, tdData.length - 1);
                });
                tdData += ") VALUES ";
                // Row vs Column
                $(el).find('tbody').find('tr').each(function () {
                    tdData += "(";
                    $(this).filter(':visible').find('td').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                tdData += '"' + parseString($(this)) + '",';
                            }
                        }
                    });

                    tdData = $.trim(tdData).substring(0, tdData.length - 1);
                    tdData += "),";
                });
                tdData = $.trim(tdData).substring(0, tdData.length - 1);
                tdData += ";";

                //output
                //console.log(tdData);

                if (defaults.consoleLog == 'true') {
                    console.log(tdData);
                }

                var base64data = "base64," + $.base64.encode(tdData);
                window.open('data:application/sql;filename=exportData;' + base64data);


            } else if (defaults.type == 'json') {

                var jsonHeaderArray = [];
                $(el).find('thead').find('tr').each(function () {
                    var tdData = "";
                    var jsonArrayTd = [];

                    $(this).filter(':visible').find('th').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                jsonArrayTd.push(parseString($(this)));
                            }
                        }
                    });
                    jsonHeaderArray.push(jsonArrayTd);

                });

                var jsonArray = [];
                $(el).find('tbody').find('tr').each(function () {
                    var tdData = "";
                    var jsonArrayTd = [];

                    $(this).filter(':visible').find('td').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                jsonArrayTd.push(parseString($(this)));
                            }
                        }
                    });
                    jsonArray.push(jsonArrayTd);

                });

                var jsonExportArray = [];
                jsonExportArray.push({header: jsonHeaderArray, data: jsonArray});

                //Return as JSON
                //console.log(JSON.stringify(jsonExportArray));

                //Return as Array
                //console.log(jsonExportArray);
                if (defaults.consoleLog == 'true') {
                    console.log(JSON.stringify(jsonExportArray));
                }
                var base64data = "base64," + $.base64.encode(JSON.stringify(jsonExportArray));
                window.open('data:application/json;filename=exportData;' + base64data);
            } else if (defaults.type == 'xml') {

                var xml = '<?xml version="1.0" encoding="utf-8"?>';
                xml += '<tabledata><fields>';

                // Header
                $(el).find('thead').find('tr').each(function () {
                    $(this).filter(':visible').find('th').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                xml += "<field>" + parseString($(this)) + "</field>";
                            }
                        }
                    });
                });
                xml += '</fields><data>';

                // Row Vs Column
                var rowCount = 1;
                $(el).find('tbody').find('tr').each(function () {
                    xml += '<row id="' + rowCount + '">';
                    var colCount = 0;
                    $(this).filter(':visible').find('td').each(function (index, data) {
                        if ($(this).css('display') != 'none') {
                            if (defaults.ignoreColumn.indexOf(index) == -1) {
                                xml += "<column-" + colCount + ">" + parseString($(this)) + "</column-" + colCount + ">";
                            }
                        }
                        colCount++;
                    });
                    rowCount++;
                    xml += '</row>';
                });
                xml += '</data></tabledata>'

                if (defaults.consoleLog == 'true') {
                    console.log(xml);
                }

                var base64data = "base64," + $.base64.encode(xml);
                window.open('data:application/xml;filename=exportData;' + base64data);

            } else if (defaults.type == 'excel' || defaults.type == 'xls' || defaults.type == 'word' || defaults.type == 'doc') {

                var MSDocType = (defaults.type == 'excel' || defaults.type == 'xls') ? 'excel' : 'word';
                var MSDocExt = (MSDocType == 'excel') ? 'xls' : 'doc';
                var MSDocSchema = (MSDocExt == 'xls') ? 'xmlns:x="urn:schemas-microsoft-com:office:excel"' : 'xmlns:w="urn:schemas-microsoft-com:office:word"';
                var $tables = $(el).filter(function () {
                    return $(this).data("tableexport-display") != 'none' &&
                            ($(this).is(':visible') ||
                                    $(this).data("tableexport-display") == 'always');
                });
                var docData = '';

                $tables.each(function () {
                    rowIndex = 0;

                    colNames = GetColumnNames(this);

                    docData += '<table><thead>';
                    // Header
                    $hrows = $(this).find('thead').first().find(defaults.theadSelector);
                    $hrows.each(function () {
                        trData = "";
                        ForEachVisibleCell(this, 'th,td', rowIndex, $hrows.length,
                                function (cell, row, col) {
                                    if (cell != null) {
                                        var thstyle = '';
                                        trData += '<th';
                                        for (var styles in defaults.excelstyles) {
                                            if (defaults.excelstyles.hasOwnProperty(styles)) {
                                                var thcss = $(cell).css(defaults.excelstyles[styles]);
                                                if (thcss != '' && thcss != '0px none rgb(0, 0, 0)') {
                                                    if (thstyle == '')
                                                        thstyle = 'style="';
                                                    thstyle += defaults.excelstyles[styles] + ':' + thcss + ';';
                                                }
                                            }
                                        }
                                        if (thstyle != '')
                                            trData += ' ' + thstyle + '"';
                                        if ($(cell).is("[colspan]"))
                                            trData += ' colspan="' + $(cell).attr('colspan') + '"';
                                        if ($(cell).is("[rowspan]"))
                                            trData += ' rowspan="' + $(cell).attr('rowspan') + '"';
                                        trData += '>' + parseString2(cell, row, col) + '</th>';
                                    }
                                });
                        if (trData.length > 0)
                            docData += '<tr>' + trData + '</tr>';
                        rowIndex++;
                    });

                    docData += '</thead><tbody>';
                    // Row Vs Column
                    $rows = $(this).find('tbody').first().find(defaults.tbodySelector);
                    $rows.each(function () {
                        trData = "";
                        ForEachVisibleCell(this, 'td', rowIndex, $hrows.length + $rows.length,
                                function (cell, row, col) {
                                    if (cell != null) {
                                        var tdstyle = '';
                                        var tdcss = $(cell).data("tableexport-msonumberformat");

                                        if (typeof tdcss == 'undefined' && typeof defaults.onMsoNumberFormat === 'function')
                                            tdcss = defaults.onMsoNumberFormat(cell, row, col);

                                        if (typeof tdcss != 'undefined' && tdcss != '') {
                                            if (tdstyle == '')
                                                tdstyle = 'style="';
                                            tdstyle = 'style="mso-number-format:' + tdcss + ';';
                                        }

                                        trData += '<td';
                                        for (var styles in defaults.excelstyles) {
                                            if (defaults.excelstyles.hasOwnProperty(styles)) {
                                                tdcss = $(cell).css(defaults.excelstyles[styles]);
                                                if (tdcss != '' && tdcss != '0px none rgb(0, 0, 0)') {
                                                    if (tdstyle == '')
                                                        tdstyle = 'style="';
                                                    tdstyle += defaults.excelstyles[styles] + ':' + tdcss + ';';
                                                }
                                            }
                                        }
                                        if (tdstyle != '')
                                            trData += ' ' + tdstyle + '"';
                                        if ($(cell).is("[colspan]"))
                                            trData += ' colspan="' + $(cell).attr('colspan') + '"';
                                        if ($(cell).is("[rowspan]"))
                                            trData += ' rowspan="' + $(cell).attr('rowspan') + '"';
                                        trData += '>' + parseString2(cell, row, col) + '</td>';
                                    }
                                });
                        if (trData.length > 0)
                            docData += '<tr>' + trData + '</tr>';
                        rowIndex++;
                    });

                    if (defaults.displayTableName)
                        docData += '<tr><td></td></tr><tr><td></td></tr><tr><td>' + parseString2($('<p>' + defaults.tableName + '</p>')) + '</td></tr>';

                    docData += '</tbody></table>';

                    if (defaults.consoleLog === true)
                        console.log(docData);
                });

                var docFile = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ' + MSDocSchema + ' xmlns="http://www.w3.org/TR/REC-html40">';
                docFile += '<meta http-equiv="content-type" content="application/vnd.ms-' + MSDocType + '; charset=UTF-8">';
                docFile += "<head>";
                if (MSDocType === 'excel') {
                    docFile += "<!--[if gte mso 9]>";
                    docFile += "<xml>";
                    docFile += "<x:ExcelWorkbook>";
                    docFile += "<x:ExcelWorksheets>";
                    docFile += "<x:ExcelWorksheet>";
                    docFile += "<x:Name>";
                    docFile += defaults.worksheetName;
                    docFile += "</x:Name>";
                    docFile += "<x:WorksheetOptions>";
                    docFile += "<x:DisplayGridlines/>";
                    docFile += "</x:WorksheetOptions>";
                    docFile += "</x:ExcelWorksheet>";
                    docFile += "</x:ExcelWorksheets>";
                    docFile += "</x:ExcelWorkbook>";
                    docFile += "</xml>";
                    docFile += "<![endif]-->";
                }
                docFile += "</head>";
                docFile += "<body>";
                docFile += docData;
                docFile += "</body>";
                docFile += "</html>";

                if (defaults.consoleLog === true)
                    console.log(docFile);

                if (defaults.outputMode === 'string')
                    return docFile;

                if (defaults.outputMode === 'base64')
                    return base64encode(docFile);

                try {
                    var blob = new Blob([docFile], {type: 'application/vnd.ms-' + defaults.type});
                    saveAs(blob, defaults.fileName + '.' + MSDocExt);
                } catch (e) {
                    downloadFile(defaults.fileName + '.' + MSDocExt,
                            'data:application/vnd.ms-' + MSDocType + ';base64,',
                            docFile);
                }

            } else if (defaults.type == 'png') {
                html2canvas($(el), {
                    onrendered: function (canvas) {
                        var img = canvas.toDataURL("image/png");
                        window.open(img);


                    }
                });
            } else if (defaults.type == 'pdf') {
                if (defaults.jspdf.autotable === true) {
                    //no funciona
                    var addHtmlOptions = {
                        dim: {
                            w: getPropertyUnitValue($(el).first().get(0), 'width', 'mm'),
                            h: getPropertyUnitValue($(el).first().get(0), 'height', 'mm')
                        },
                        pagesplit: false
                    };

                    var doc = new jsPDF(defaults.jspdf.orientation, defaults.jspdf.unit, defaults.jspdf.format);
                    doc.addHTML($(el).first(),
                            defaults.jspdf.margins.left,
                            defaults.jspdf.margins.top,
                            addHtmlOptions,
                            function () {
                                jsPdfOutput(doc);
                            });
                    //delete doc;
                } else
                {
                    /*
                     * esto ya funciona
                     * 
                     //var doc = new jsPDF('p', 'pt', 'a4', true);
                     var doc = new jsPDF(defaults.jspdf.orientation, defaults.jspdf.unit, defaults.jspdf.format);
                     doc.setFontSize(defaults.pdfFontSize);
                     
                     // Header
                     var startColPosition = defaults.pdfLeftMargin;
                     $(el).find('thead').find('tr').each(function () {
                     $(this).filter(':visible').find('th').each(function (index, data) {
                     if ($(this).css('display') != 'none') {
                     if (defaults.ignoreColumn.indexOf(index) == -1) {
                     var colPosition = startColPosition + (index * 138);
                     doc.text(colPosition, 20, parseString($(this)));
                     }
                     }
                     });
                     });
                     
                     
                     // Row Vs Column
                     var startRowPosition = 20;
                     var page = 1;
                     var rowPosition = 0;
                     $(el).find('tbody').find('tr').each(function (index, data) {
                     rowCalc = index + 1;
                     
                     if (rowCalc % 100 == 0) {
                     doc.addPage();
                     page++;
                     startRowPosition = startRowPosition + 10;
                     }
                     rowPosition = (startRowPosition + (rowCalc * 10)) - ((page - 1) * 280);
                     
                     $(this).filter(':visible').find('td').each(function (index, data) {
                     if ($(this).css('display') != 'none') {
                     if (defaults.ignoreColumn.indexOf(index) == -1) {
                     var colPosition = startColPosition + (index * 138);
                     doc.text(colPosition, rowPosition, parseString($(this)));
                     }
                     }
                     
                     });
                     
                     });
                     
                     // Output as Data URI
                     doc.output('datauri');
                     */


                    // pdf output using jsPDF AutoTable plugin
                    // https://github.com/simonbengtsson/jsPDF-AutoTable

                    var teOptions = defaults.jspdf.autotable.tableExport;

                    // When setting jspdf.format to 'bestfit' tableExport tries to choose
                    // the minimum required paper format and orientation in which the table
                    // (or tables in multitable mode) completely fits without column adjustment
                    if (typeof defaults.jspdf.format === 'string' && defaults.jspdf.format.toLowerCase() === 'bestfit') {
                        var pageFormats = {
                            'a0': [2383.94, 3370.39], 'a1': [1683.78, 2383.94],
                            'a2': [1190.55, 1683.78], 'a3': [841.89, 1190.55],
                            'a4': [595.28, 841.89]
                        };
                        var rk = '', ro = '';
                        var mw = 0;

                        $(el).filter(':visible').each(function () {
                            if ($(this).css('display') != 'none') {
                                var w = getPropertyUnitValue($(this).get(0), 'width', 'pt');

                                if (w > mw) {
                                    if (w > pageFormats['a0'][0]) {
                                        rk = 'a0';
                                        ro = 'l';
                                    }
                                    for (var key in pageFormats) {
                                        if (pageFormats.hasOwnProperty(key)) {
                                            if (pageFormats[key][1] > w) {
                                                rk = key;
                                                ro = 'l';
                                                if (pageFormats[key][0] > w)
                                                    ro = 'p';
                                            }
                                        }
                                    }
                                    mw = w;
                                }
                            }
                        });
                        defaults.jspdf.format = (rk == '' ? 'a4' : rk);
                        defaults.jspdf.orientation = (ro == '' ? 'w' : ro);
                    }

                    // The jsPDF doc object is stored in defaults.jspdf.autotable.tableExport,
                    // thus it can be accessed from any callback function
                    teOptions.doc = new jsPDF(defaults.jspdf.orientation,
                            defaults.jspdf.unit,
                            defaults.jspdf.format);

                    $(el).filter(function () {
                        return $(this).data("tableexport-display") != 'none' &&
                                ($(this).is(':visible') ||
                                        $(this).data("tableexport-display") == 'always');
                    }).each(function () {
                        var colKey;
                        var rowIndex = 0;

                        colNames = GetColumnNames(this);

                        teOptions.columns = [];
                        teOptions.rows = [];
                        teOptions.rowoptions = {};

                        // onTable: optional callback function for every matching table that can be used
                        // to modify the tableExport options or to skip the output of a particular table
                        // if the table selector targets multiple tables
                        if (typeof teOptions.onTable === 'function')
                            if (teOptions.onTable($(this), defaults) === false)
                                return true; // continue to next iteration step (table)

                        // each table works with an own copy of AutoTable options
                        defaults.jspdf.autotable.tableExport = null;  // avoid deep recursion error
                        var atOptions = $.extend(true, {}, defaults.jspdf.autotable);
                        defaults.jspdf.autotable.tableExport = teOptions;

                        atOptions.margin = {};
                        $.extend(true, atOptions.margin, defaults.jspdf.margins);
                        atOptions.tableExport = teOptions;

                        // Fix jsPDF Autotable's row height calculation
                        if (typeof atOptions.beforePageContent !== 'function') {
                            atOptions.beforePageContent = function (data) {
                                if (data.pageCount == 1) {
                                    var all = data.table.rows.concat(data.table.headerRow);
                                    all.forEach(function (row) {
                                        if (row.height > 0) {
                                            row.height += (2 - FONT_ROW_RATIO) / 2 * row.styles.fontSize;
                                            data.table.height += (2 - FONT_ROW_RATIO) / 2 * row.styles.fontSize;
                                        }
                                    });
                                }
                            }
                        }

                        if (typeof atOptions.createdHeaderCell !== 'function') {
                            // apply some original css styles to pdf header cells
                            atOptions.createdHeaderCell = function (cell, data) {

                                // jsPDF AutoTable plugin v2.0.14 fix: each cell needs its own styles object
                                cell.styles = $.extend({}, data.row.styles);

                                if (typeof teOptions.columns [data.column.dataKey] != 'undefined') {
                                    var col = teOptions.columns [data.column.dataKey];

                                    if (typeof col.rect != 'undefined') {
                                        var rh;

                                        cell.contentWidth = col.rect.width;

                                        if (typeof teOptions.heightRatio == 'undefined' || teOptions.heightRatio == 0) {
                                            if (data.row.raw [data.column.dataKey].rowspan)
                                                rh = data.row.raw [data.column.dataKey].rect.height / data.row.raw [data.column.dataKey].rowspan;
                                            else
                                                rh = data.row.raw [data.column.dataKey].rect.height;

                                            teOptions.heightRatio = cell.styles.rowHeight / rh;
                                        }

                                        rh = data.row.raw [data.column.dataKey].rect.height * teOptions.heightRatio;
                                        if (rh > cell.styles.rowHeight)
                                            cell.styles.rowHeight = rh;
                                    }

                                    if (typeof col.style != 'undefined' && col.style.hidden !== true) {
                                        cell.styles.halign = col.style.align;
                                        if (atOptions.styles.fillColor === 'inherit')
                                            cell.styles.fillColor = col.style.bcolor;
                                        if (atOptions.styles.textColor === 'inherit')
                                            cell.styles.textColor = col.style.color;
                                        if (atOptions.styles.fontStyle === 'inherit')
                                            cell.styles.fontStyle = col.style.fstyle;
                                    }
                                }
                            }
                        }

                        if (typeof atOptions.createdCell !== 'function') {
                            // apply some original css styles to pdf table cells
                            atOptions.createdCell = function (cell, data) {
                                var rowopt = teOptions.rowoptions [data.row.index + ":" + data.column.dataKey];

                                if (typeof rowopt != 'undefined' &&
                                        typeof rowopt.style != 'undefined' &&
                                        rowopt.style.hidden !== true) {
                                    cell.styles.halign = rowopt.style.align;
                                    if (atOptions.styles.fillColor === 'inherit')
                                        cell.styles.fillColor = rowopt.style.bcolor;
                                    if (atOptions.styles.textColor === 'inherit')
                                        cell.styles.textColor = rowopt.style.color;
                                    if (atOptions.styles.fontStyle === 'inherit')
                                        cell.styles.fontStyle = rowopt.style.fstyle;
                                }
                            }
                        }

                        if (typeof atOptions.drawHeaderCell !== 'function') {
                            atOptions.drawHeaderCell = function (cell, data) {
                                var colopt = teOptions.columns [data.column.dataKey];

                                if ((colopt.style.hasOwnProperty("hidden") != true || colopt.style.hidden !== true) &&
                                        colopt.rowIndex >= 0)
                                    return prepareAutoTableText(cell, data, colopt);
                                else
                                    return false; // cell is hidden
                            }
                        }

                        if (typeof atOptions.drawCell !== 'function') {
                            atOptions.drawCell = function (cell, data) {
                                var rowopt = teOptions.rowoptions [data.row.index + ":" + data.column.dataKey];
                                if (prepareAutoTableText(cell, data, rowopt)) {

                                    teOptions.doc.rect(cell.x, cell.y, cell.width, cell.height, cell.styles.fillStyle);

                                    if (typeof rowopt != 'undefined' && typeof rowopt.kids != 'undefined' && rowopt.kids.length > 0) {

                                        var dh = cell.height / rowopt.rect.height;
                                        if (dh > teOptions.dh || typeof teOptions.dh == 'undefined')
                                            teOptions.dh = dh;
                                        teOptions.dw = cell.width / rowopt.rect.width;

                                        drawCellElements(cell, rowopt.kids, teOptions);
                                    }
                                    teOptions.doc.autoTableText(cell.text, cell.textPos.x, cell.textPos.y, {
                                        halign: cell.styles.halign,
                                        valign: cell.styles.valign
                                    });
                                }
                                return false;
                            }
                        }

                        // collect header and data rows
                        teOptions.headerrows = [];
                        $hrows = $(this).find('thead').find(defaults.theadSelector);
                        $hrows.each(function () {
                            colKey = 0;

                            teOptions.headerrows[rowIndex] = [];

                            ForEachVisibleCell(this, 'th,td', rowIndex, $hrows.length,
                                    function (cell, row, col) {
                                        var obj = getCellStyles(cell);
                                        obj.title = parseString2(cell, row, col);
                                        obj.key = colKey++;
                                        obj.rowIndex = rowIndex;
                                        teOptions.headerrows[rowIndex].push(obj);
                                    });
                            rowIndex++;
                        });

                        if (rowIndex > 0) {
                            // iterate through last row
                            $.each(teOptions.headerrows[rowIndex - 1], function () {
                                if (rowIndex > 1 && this.rect == null)
                                    obj = teOptions.headerrows[rowIndex - 2][this.key];
                                else
                                    obj = this;

                                if (obj != null)
                                    teOptions.columns.push(obj);
                            });
                        }

                        var rowCount = 0;
                        $rows = $(this).find('tbody').find(defaults.tbodySelector);
                        $rows.each(function () {
                            var rowData = [];
                            colKey = 0;

                            ForEachVisibleCell(this, 'td', rowIndex, $hrows.length + $rows.length,
                                    function (cell, row, col) {
                                        if (typeof teOptions.columns[colKey] === 'undefined') {
                                            // jsPDF-Autotable needs columns. Thus define hidden ones for tables without thead
                                            var obj = {
                                                title: '',
                                                key: colKey,
                                                style: {
                                                    hidden: true
                                                }
                                            };
                                            teOptions.columns.push(obj);
                                        }
                                        if (typeof cell !== 'undefined' && cell != null) {
                                            var obj = getCellStyles(cell);
                                            obj.kids = $(cell).children();
                                            teOptions.rowoptions [rowCount + ":" + colKey++] = obj;
                                        } else {
                                            var obj = $.extend(true, {}, teOptions.rowoptions [rowCount + ":" + (colKey - 1)]);
                                            obj.colspan = -1;
                                            teOptions.rowoptions [rowCount + ":" + colKey++] = obj;
                                        }

                                        rowData.push(parseString2(cell, row, col));
                                    });
                            if (rowData.length) {
                                teOptions.rows.push(rowData);
                                rowCount++
                            }
                            rowIndex++;
                        });

                        // onBeforeAutotable: optional callback function before calling
                        // jsPDF AutoTable that can be used to modify the AutoTable options
                        if (typeof teOptions.onBeforeAutotable === 'function')
                            teOptions.onBeforeAutotable($(this), teOptions.columns, teOptions.rows, atOptions);

                        teOptions.doc.autoTable(teOptions.columns, teOptions.rows, atOptions);

                        // onAfterAutotable: optional callback function after returning
                        // from jsPDF AutoTable that can be used to modify the AutoTable options
                        if (typeof teOptions.onAfterAutotable === 'function')
                            teOptions.onAfterAutotable($(this), atOptions);

                        // set the start position for the next table (in case there is one)
                        defaults.jspdf.autotable.startY = teOptions.doc.autoTableEndPosY() + atOptions.margin.top;
                    });

                    jsPdfOutput(teOptions.doc);

                    if (typeof teOptions.headerrows != 'undefined')
                        teOptions.headerrows.length = 0;
                    if (typeof teOptions.columns != 'undefined')
                        teOptions.columns.length = 0;
                    if (typeof teOptions.rows != 'undefined')
                        teOptions.rows.length = 0;
                    delete teOptions.doc;
                    teOptions.doc = null;

                }

            }


            function parseString(data) {

                if (defaults.htmlContent == 'true') {
                    content_data = data.html().trim();
                } else {
                    content_data = data.text().trim();
                }

                if (defaults.escape == 'true') {
                    content_data = escape(content_data);
                }



                return content_data;
            }

            function parseString2(cell, rowIndex, colIndex) {
                var result = '';

                if (cell != null) {
                    var $cell = $(cell);
                    var htmlData;

                    if ($cell[0].hasAttribute("data-tableexport-value"))
                        htmlData = $cell.data("tableexport-value");
                    else
                        htmlData = $cell.html();

                    if (typeof defaults.onCellHtmlData === 'function')
                        htmlData = defaults.onCellHtmlData($cell, rowIndex, colIndex, htmlData);

                    if (defaults.htmlContent === true) {
                        result = $.trim(htmlData);
                    } else {
                        var text = htmlData.replace(/\n/g, '\u2028').replace(/<br\s*[\/]?>/gi, '\u2060');
                        var obj = $('<div/>').html(text).contents();
                        text = '';
                        $.each(obj.text().split("\u2028"), function (i, v) {
                            if (i > 0)
                                text += " ";
                            text += $.trim(v);
                        });

                        $.each(text.split("\u2060"), function (i, v) {
                            if (i > 0)
                                result += "\n";
                            result += $.trim(v).replace(/\u00AD/g, ""); // remove soft hyphens
                        });

                        if (defaults.numbers.html.decimalMark != defaults.numbers.output.decimalMark ||
                                defaults.numbers.html.thousandsSeparator != defaults.numbers.output.thousandsSeparator) {
                            var number = parseNumber(result);

                            if (number !== false) {
                                var frac = ("" + number).split('.');
                                if (frac.length == 1)
                                    frac[1] = "";
                                var mod = frac[0].length > 3 ? frac[0].length % 3 : 0;

                                result = (number < 0 ? "-" : "") +
                                        (defaults.numbers.output.thousandsSeparator ? ((mod ? frac[0].substr(0, mod) + defaults.numbers.output.thousandsSeparator : "") + frac[0].substr(mod).replace(/(\d{3})(?=\d)/g, "$1" + defaults.numbers.output.thousandsSeparator)) : frac[0]) +
                                        (frac[1].length ? defaults.numbers.output.decimalMark + frac[1] : "");
                            }
                        }
                    }

                    if (defaults.escape === true) {
                        result = escape(result);
                    }

                    if (typeof defaults.onCellData === 'function') {
                        result = defaults.onCellData($cell, rowIndex, colIndex, result);
                    }
                }

                return result;
            }


            function GetColumnNames(table) {
                var result = [];
                $(table).find('thead').first().find('th').each(function (index, el) {
                    if ($(el).attr("data-field") !== undefined)
                        result[index] = $(el).attr("data-field");
                });
                return result;
            }
            function downloadFile(filename, header, data) {

                var ua = window.navigator.userAgent;
                if (ua.indexOf("MSIE ") > 0 || !!ua.match(/Trident.*rv\:11\./)) {
                    // Internet Explorer (<= 9) workaround by Darryl (https://github.com/dawiong/tableExport.jquery.plugin)
                    // based on sampopes answer on http://stackoverflow.com/questions/22317951
                    // ! Not working for json and pdf format !
                    var frame = document.createElement("iframe");

                    if (frame) {
                        document.body.appendChild(frame);
                        frame.setAttribute("style", "display:none");
                        frame.contentDocument.open("txt/html", "replace");
                        frame.contentDocument.write(data);
                        frame.contentDocument.close();
                        frame.focus();

                        frame.contentDocument.execCommand("SaveAs", true, filename);
                        document.body.removeChild(frame);
                    }
                } else {
                    var DownloadLink = document.createElement('a');

                    if (DownloadLink) {
                        DownloadLink.style.display = 'none';
                        DownloadLink.download = filename;

                        if (header.toLowerCase().indexOf("base64,") >= 0)
                            DownloadLink.href = header + base64encode(data);
                        else
                            DownloadLink.href = header + encodeURIComponent(data);

                        document.body.appendChild(DownloadLink);

                        if (document.createEvent) {
                            if (DownloadEvt == null)
                                DownloadEvt = document.createEvent('MouseEvents');

                            DownloadEvt.initEvent('click', true, false);
                            DownloadLink.dispatchEvent(DownloadEvt);
                        } else if (document.createEventObject)
                            DownloadLink.fireEvent('onclick');
                        else if (typeof DownloadLink.onclick == 'function')
                            DownloadLink.onclick();

                        document.body.removeChild(DownloadLink);
                    }
                }
            }

            function base64encode(input) {
                var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
                var output = "";
                var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
                var i = 0;
                input = utf8Encode(input);
                while (i < input.length) {
                    chr1 = input.charCodeAt(i++);
                    chr2 = input.charCodeAt(i++);
                    chr3 = input.charCodeAt(i++);
                    enc1 = chr1 >> 2;
                    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                    enc4 = chr3 & 63;
                    if (isNaN(chr2)) {
                        enc3 = enc4 = 64;
                    } else if (isNaN(chr3)) {
                        enc4 = 64;
                    }
                    output = output +
                            keyStr.charAt(enc1) + keyStr.charAt(enc2) +
                            keyStr.charAt(enc3) + keyStr.charAt(enc4);
                }
                return output;
            }

            function utf8Encode(string) {
                string = string.replace(/\x0d\x0a/g, "\x0a");
                var utftext = "";
                for (var n = 0; n < string.length; n++) {
                    var c = string.charCodeAt(n);
                    if (c < 128) {
                        utftext += String.fromCharCode(c);
                    } else if ((c > 127) && (c < 2048)) {
                        utftext += String.fromCharCode((c >> 6) | 192);
                        utftext += String.fromCharCode((c & 63) | 128);
                    } else {
                        utftext += String.fromCharCode((c >> 12) | 224);
                        utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                        utftext += String.fromCharCode((c & 63) | 128);
                    }
                }
                return utftext;
            }

            function ForEachVisibleCell(tableRow, selector, rowIndex, rowCount, cellcallback) {
                if ($.inArray(rowIndex, defaults.ignoreRow) == -1 &&
                        $.inArray(rowIndex - rowCount, defaults.ignoreRow) == -1) {

                    var $row = $(tableRow).filter(function () {
                        return $(this).data("tableexport-display") != 'none' &&
                                ($(this).is(':visible') ||
                                        $(this).data("tableexport-display") == 'always' ||
                                        $(this).closest('table').data("tableexport-display") == 'always');
                    }).find(selector);

                    var rowColspan = 0;
                    var rowColIndex = 0;

                    $row.each(function (colIndex) {
                        if ($(this).data("tableexport-display") == 'always' ||
                                ($(this).css('display') != 'none' &&
                                        $(this).css('visibility') != 'hidden' &&
                                        $(this).data("tableexport-display") != 'none')) {
                            if (isColumnIgnored($row, colIndex) == false) {
                                if (typeof (cellcallback) === "function") {
                                    var c, Colspan = 0;
                                    var r, Rowspan = 0;

                                    // handle rowspans from previous rows
                                    if (typeof rowspans[rowIndex] != 'undefined' && rowspans[rowIndex].length > 0) {
                                        for (c = 0; c <= colIndex; c++) {
                                            if (typeof rowspans[rowIndex][c] != 'undefined') {
                                                cellcallback(null, rowIndex, c);
                                                delete rowspans[rowIndex][c];
                                                colIndex++;
                                            }
                                        }
                                    }
                                    rowColIndex = colIndex;

                                    if ($(this).is("[colspan]")) {
                                        Colspan = parseInt($(this).attr('colspan'));
                                        rowColspan += Colspan > 0 ? Colspan - 1 : 0;
                                    }

                                    if ($(this).is("[rowspan]"))
                                        Rowspan = parseInt($(this).attr('rowspan'));

                                    // output content of current cell
                                    cellcallback(this, rowIndex, colIndex);

                                    // handle colspan of current cell
                                    for (c = 0; c < Colspan - 1; c++)
                                        cellcallback(null, rowIndex, colIndex + c);

                                    // store rowspan for following rows
                                    if (Rowspan) {
                                        for (r = 1; r < Rowspan; r++) {
                                            if (typeof rowspans[rowIndex + r] == 'undefined')
                                                rowspans[rowIndex + r] = [];

                                            rowspans[rowIndex + r][colIndex + rowColspan] = "";

                                            for (c = 1; c < Colspan; c++)
                                                rowspans[rowIndex + r][colIndex + rowColspan - c] = "";
                                        }
                                    }
                                }
                            }
                        }
                    });
                    // handle rowspans from previous rows
                    if (typeof rowspans[rowIndex] != 'undefined' && rowspans[rowIndex].length > 0) {
                        for (c = 0; c <= rowspans[rowIndex].length; c++) {
                            if (typeof rowspans[rowIndex][c] != 'undefined') {
                                cellcallback(null, rowIndex, c);
                                delete rowspans[rowIndex][c];
                            }
                        }
                    }
                }
            }

            function isColumnIgnored($row, colIndex) {
                var result = false;
                if (defaults.ignoreColumn.length > 0) {
                    if (typeof defaults.ignoreColumn[0] == 'string') {
                        if (colNames.length > colIndex && typeof colNames[colIndex] != 'undefined')
                            if ($.inArray(colNames[colIndex], defaults.ignoreColumn) != -1)
                                result = true;
                    } else if (typeof defaults.ignoreColumn[0] == 'number') {
                        if ($.inArray(colIndex, defaults.ignoreColumn) != -1 ||
                                $.inArray(colIndex - $row.length, defaults.ignoreColumn) != -1)
                            result = true;
                    }
                }
                return result;
            }
            function getPropertyUnitValue(target, prop, unit) {
                var value = getStyle(target, prop);  // get the computed style value

                var numeric = value.match(/\d+/);  // get the numeric component
                if (numeric !== null) {
                    numeric = numeric[0];  // get the string

                    return getUnitValue(target.parentElement, numeric, unit);
                }
                return 0;
            }

            function getStyle(target, prop) {
                try {
                    if (window.getComputedStyle) { // gecko and webkit
                        prop = prop.replace(/([a-z])([A-Z])/, hyphenate);  // requires hyphenated, not camel
                        return window.getComputedStyle(target, null).getPropertyValue(prop);
                    }
                    if (target.currentStyle) { // ie
                        return target.currentStyle[prop];
                    }
                    return target.style[prop];
                } catch (e) {
                }
                return "";
            }

            function getCellStyles(cell) {
                var a = getStyle(cell, 'text-align');
                var fw = getStyle(cell, 'font-weight');
                var fs = getStyle(cell, 'font-style');
                var f = '';
                if (a == 'start')
                    a = getStyle(cell, 'direction') == 'rtl' ? 'right' : 'left';
                if (fw >= 700)
                    f = 'bold';
                if (fs == 'italic')
                    f += fs;
                if (f == '')
                    f = 'normal';

                var result = {
                    style: {
                        align: a,
                        bcolor: rgb2array(getStyle(cell, 'background-color'), [255, 255, 255]),
                        color: rgb2array(getStyle(cell, 'color'), [0, 0, 0]),
                        fstyle: f
                    },
                    colspan: (parseInt($(cell).attr('colspan')) || 0),
                    rowspan: (parseInt($(cell).attr('rowspan')) || 0)
                };

                if (cell !== null) {
                    var r = cell.getBoundingClientRect();
                    result.rect = {
                        width: r.width,
                        height: r.height
                    };
                }

                return result;
            }

            function hyphenate(a, b, c) {
                return b + "-" + c.toLowerCase();
            }

            function rgb2array(rgb_string, default_result) {
                var re = /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/;
                var bits = re.exec(rgb_string);
                var result = default_result;
                if (bits)
                    result = [parseInt(bits[1]), parseInt(bits[2]), parseInt(bits[3])];
                return result;
            }

            function prepareAutoTableText(cell, data, cellopt) {
                var cs = 0;
                if (typeof cellopt != 'undefined')
                    cs = cellopt.colspan;

                if (cs >= 0) {
                    // colspan handling
                    var cellWidth = cell.width;
                    var textPosX = cell.textPos.x;
                    var i = data.table.columns.indexOf(data.column);

                    for (var c = 1; c < cs; c++) {
                        var column = data.table.columns[i + c];
                        cellWidth += column.width;
                    }

                    if (cs > 1) {
                        if (cell.styles.halign === 'right')
                            textPosX = cell.textPos.x + cellWidth - cell.width;
                        else if (cell.styles.halign === 'center')
                            textPosX = cell.textPos.x + (cellWidth - cell.width) / 2;
                    }

                    cell.width = cellWidth;
                    cell.textPos.x = textPosX;

                    if (typeof cellopt != 'undefined' && cellopt.rowspan > 1)
                        cell.height = cell.height * cellopt.rowspan;

                    // fix jsPDF's calculation of text position
                    if (cell.styles.valign === 'middle' || cell.styles.valign === 'bottom') {
                        var splittedText = typeof cell.text === 'string' ? cell.text.split(/\r\n|\r|\n/g) : cell.text;
                        var lineCount = splittedText.length || 1;
                        if (lineCount > 2)
                            cell.textPos.y -= ((2 - FONT_ROW_RATIO) / 2 * data.row.styles.fontSize) * (lineCount - 2) / 3;
                    }
                    return true;
                } else
                    return false; // cell is hidden (colspan = -1), don't draw it
            }

            function jsPdfOutput(doc) {
                if (defaults.consoleLog === true)
                    console.log(doc.output());

                if (defaults.outputMode === 'string')
                    return doc.output();

                if (defaults.outputMode === 'base64')
                    return base64encode(doc.output());

                try {
                    //var blob = doc.output('blob');
                    //saveAs(blob, defaults.fileName + '.pdf');
                    //doc.output('datauri'); //abrre el pdf en la misma ventana
                    //doc.output('save', 'filename.pdf'); //intenta guardar el pdf como un archivo (no funciona on ie menor a 10 y algunos moviles)
                    //doc.output('datauristring');        //regresa la direccion en un string
                    doc.output('dataurlnewwindow');     //abre el pdf en una nueva ventana
                    
                } catch (e) {
                    downloadFile(defaults.fileName + '.pdf',
                            'data:application/pdf;base64,',
                            doc.output());
                            
                }
            }

        }
    });
})(jQuery);

