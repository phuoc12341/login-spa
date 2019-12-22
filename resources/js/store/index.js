import Vue from "vue";
import Vuex from "vuex";
// import Language from "./Language.module.js";
import Authentication from "./Authentication.module";

Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        // language: Language
        authentication: Authentication
    }
});

export default store;
