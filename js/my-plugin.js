//app = utilityVueComponent(app);

function utilityVueComponent(app) {
    app.component('mess', {
        props: ['show', 'title', 'message'],
        template: `
        <div id="dialogMessageModal" class="modal fade show" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ title }}</h5>
                        <button type="button" class="close" @click="hideDialog" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p v-html="message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="hideDialog">OK</button>
                    </div>
                </div>
            </div>
        </div>
    `,
        methods: {
            hideDialog() {
                $('#dialogMessageModal').modal('hide');
                this.$emit('closed',0); // Emit an event to update the 'show' prop in the parent
            }
        },
        watch: {
            show(newValue) {
                if (newValue) {

                    $('#dialogMessageModal').modal('show');
                } else {
                    $('#dialogMessageModal').modal('hide');
                }
            }

},
mounted() {
    console.log('Message component mounted');
}
});

    app.component('Loader', {
        props: ['loading', 'text'],
        template: `
            <span v-if="loading" class="loader" :title="text" style="height: 1em; width: 1em;"></span>
        `
        ,
        mounted() {
        console.log('Loading component mounted');
    }
    });
    app.component('dial', {
        props: ['show'],
        template: `
            <div id="dialogModal" class="modal fade show" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body" style="text-align: center">
                            <Loader :loading="show" />
                            <p>Please wait</p>
                        </div>
                    </div>
                </div>
            </div>
        `,
        mounted() {
            // Initialization for modal backdrop
            console.log('dialog component mounted');
            $('#dialogModal').modal({
                backdrop: 'static',
                keyboard: false,
            });
        },
        watch: {
            show(newValue) {
                if (newValue) {
                   // alert("hi");
                    $('#dialogModal').modal('show');
                } else {
                    $('#dialogModal').modal('hide');
                    //this.$emit('closed',0);
                }
            },
        },
        methods: {
            hideDialog() {
                $('#dialogModal').modal('hide');
                this.$emit('closed',0); // Emit an event to update the 'show' prop in the parent
            }
        },
    });

    return app;
};

//app.mount('#app');
