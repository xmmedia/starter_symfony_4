export default {
    created () {
        this.stateService.onTransition((state) => {
            this.state = state;
        }).start();
    },

    methods: {
        stateEvent (event, data) {
            this.stateService.send(event, data);
        },
    },
}
