<html>
<head>
    <script src="https://unpkg.com/vue@2.6.11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.min.js"></script>
    <script src="https://unpkg.com/vue-material"></script>
    <meta content="width=device-width,initial-scale=1,minimal-ui" name="viewport">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic|Material+Icons">
    <link rel="stylesheet" href="https://unpkg.com/vue-material@beta/dist/vue-material.min.css">
    <link rel="stylesheet" href="https://unpkg.com/vue-material@beta/dist/theme/default-dark.css">
</head>

<body>
<div id="app">
    <div class="loader" v-bind:class="{'visible': loading }">
        <md-progress-bar md-mode="query"></md-progress-bar>
    </div>
    <md-table v-model="rowsDisplay" :md-sort.sync="currentSort" :md-sort-order.sync="currentSortOrder" ref="mytable"
              md-card md-fixed-header>
        <md-chip v-if="filesize">{{ readableFilesize() }}
            <!--needed for the inner filesize container to update-->
        </md-chip>
        <md-table-toolbar>
            <h1 class="md-title">Log Viewer
                <md-chip v-if="filesize">{{ readableFilesize() }}</md-chip>
            </h1>
            <div class="md-toolbar-section-start">
            </div>
            <md-field md-clearable class="md-toolbar-section-end">
                <md-input placeholder="Filter Rows (Regex)" v-model="search" @input="searchTable"/>
            </md-field>
            <div class="md-toolbar-section-end">
                <md-switch v-model="autoreload" class="md-primary">Autoreload</md-switch>
                <md-button @click="deleteLog()" class="md-fab md-raised md-accent"
                           :disabled="filesize == 0 ? true : false">
                    <md-icon>delete</md-icon>
                    <md-tooltip md-direction="top">Empty file. This can not be undone.</md-tooltip>
                </md-button>
            </div>
        </md-table-toolbar>
        <md-table-empty-state v-if="filesize && search" md-label="Nothing found"
                              :md-description="`No results for your search: '${search}'. Try a different search term or create a matching error ;)`">
        </md-table-empty-state>
        <md-table-empty-state v-if="!filesize" md-label="Nothing found"
                              :md-description="`Looks like the file does not exist or is empty.`">
        </md-table-empty-state>
        <md-table-empty-state v-if="error" :md-label="errorMessage">
        </md-table-empty-state>
        <md-table-empty-state v-if="loading && filesize" md-label="Loading"
                              :md-description="`The file has a size of ${ readableFilesize() }`">
        </md-table-empty-state>
        <md-table-row slot="md-table-row" slot-scope="{ item }">
            <md-table-cell :error="item.cls" md-label="Count" md-sort-by="cnt">{{ item.cnt }}</md-table-cell>
            <md-table-cell :error="item.cls" style="min-width:160px" md-label="Time" md-sort-by="time">
                <time :datetime="item.time">{{ readableDateTime(item.time) }}</time>
            </md-table-cell>
            <md-table-cell class="message" md-label="Message" md-sort-by="msg">
                <pre v-html="item.msg">{{ item.msg }}</pre>
            </md-table-cell>
        </md-table-row>
    </md-table>
</div>
<script>
    const toLower      = text => {
        return text.toString().toLowerCase()
    }
    const searchByName = (items, term) => {
        if (term) {
            var regex = new RegExp(toLower(term), 'gim');
            return items.filter(item => regex.test(item.msg));
            //return items.filter(item => toLower(item.msg).includes(toLower(term)))
        }
        return items
    }
    Vue.use(VueMaterial.default)
    var app = new Vue({
        el  : '#app',
        data: () => ({
            currentSort     : 'time',
            currentSortOrder: 'desc',
            search          : null,
            rowsRaw         : [],
            rowsDisplay     : [],
            filesize        : 0,
            loading         : false,
            delete          : '',
            autoreload      : true,
            error           : false,
            errorMessage    : 'Something went wrong',
            documentHidden  : false,
        }),
        mounted() {
            this.update(this);
            var self    = this;
            var timeout = setInterval(this.update, 4000, this);
            document.addEventListener("visibilitychange", function () {
                self.documentHidden = document.hidden;
            });
        },
        methods: {
            readableFilesize: function () {
                if (this.filesize > (1024 * 1024)) { // filesize is in bytes
                    return (Math.round(this.filesize / 1024 / 102) / 10) + ' MB';
                } else {
                    return (Math.round(this.filesize / 102) / 10) + ' KB';
                }
            },
            readableDateTime(dateTimeString) {
                let date = new Date(dateTimeString);
                return isNaN(date) ? dateTimeString : date.toLocaleString();
            },
            filterSearch() {
                if (this.search == "") {
                    return this.rowsRaw
                } else {
                    return searchByName(this.rowsRaw, this.search)
                }
            },
            searchTable() {
                this.rowsDisplay = this.filterSearch();
            },
            compareEntries() {
                const sortBy     = this.currentSort
                const multiplier = this.currentSortOrder === 'desc' ? -1 : 1;
                return (a, b) => {
                    const aAttr = a[sortBy];
                    const bAttr = b[sortBy];
                    if (aAttr === bAttr) {
                        return 0
                    } else if (typeof aAttr === 'number' && typeof bAttr === 'number') {
                        return (aAttr - bAttr) * multiplier // numerical compare, negate if descending
                    }
                    return String(aAttr).localeCompare(String(bAttr)) * multiplier;
                }
            },
            setNewData(response) {
                this.rowsRaw     = response.data
                this.loading     = false
                this.rowsDisplay = this.filterSearch();
                this.rowsDisplay.sort(this.compareEntries());
            },
            getLog(comp) {
                axios.get('?get_log&cbxwpwritelog=1').then(response => (comp.setNewData(response)))
            },
            update(comp) {
                if (!comp.autoreload | comp.documentHidden) {
                    console.log('autoreload is disabled or window is hidden');
                    return;
                }
                if (comp.loading == true) {
                    console.log('looks like loading didn\'t finish yet.');
                    return;
                }
                comp.loading = true
                comp.getFilesize((response) => {
                    let size = response.data
                    if (typeof response.data == 'string') {
                        console.log('something went wrong...')
                        comp.rowsDisplay  = [];
                        comp.error        = true
                        comp.errorMessage = response.data;
                        comp.loading      = false
                        comp.filesize     = 0;
                        return
                    }
                    if (size != comp.filesize) {
                        comp.filesize = size
                        comp.getLog(comp)
                    } else {
                        console.log('nothing changed')
                        comp.loading = false
                    }
                })
            },
            getFilesize(cb) {
                axios.get('?filesize&cbxwpwritelog=1')
                    .then(response => (cb(response)))
                    .catch(error => {
                        console.log(error.response)
                    })
            },
            deleteLog() {
                this.rowsDisplay = [];
                this.filesize    = 0;
                axios.get('?delete_log&cbxwpwritelog=1').then(response => (this.delete.data = response))
            }
        }
    })
</script>
<style>
    #app {
        padding: 0 10px;
    }

    .loader {
        transition: opacity 1s;
        opacity: 0;
    }

    .loader.visible {
        transition: opacity 0s;
        opacity: 1
    }

    .md-card {
        height: calc(100vh - 15px);
        min-width: 90vw;
        max-width: 98vw;
        overflow: hidden;
    }

    .md-content {
        max-height: unset !important;
        height: calc(100vh - 15px);
        max-width: 100%;
    }

    .md-table-cell[error*="Warning"] {
        background-color: #FFA66F;
    }

    .md-table-cell[error*="Fatal"],
    .md-table-cell[error*="error"] {
        background-color: #d05f5f !important
    }

    .md-table-cell-container,
    .md-table-head-label {
        padding-right: 10px;
    }

    pre {
        font-family: 'Roboto Mono', monospace;
        margin: 0.2rem
    }

    .md-table-cell {
        height: auto;
        vertical-align: top;
    }

    .cell-time {
        min-width: 200px
    }

    .md-table-fixed-header {
        max-width: 100%
    }

    .message {
        white-space: pre;
    }

    #app .message a {
        color: #97bfff
    }
</style>
</body>

</html>