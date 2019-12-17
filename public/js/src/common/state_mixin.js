export default {
    created () {
        this.stateService.onTransition((state) => {
            this.state = state;
        }).start();
    },

    methods: {
        stateEvent (event) {
            this.stateService.send(event);
        },
    },
}
