import Vue from "vue";
// import {
//     FavoriteService
// } from "@/common/api.service";
// import {
//     ARTICLE_RESET_STATE
// } from "./actions.type";
// import {
//     UPDATE_ARTICLE_IN_LIST
// } from "./mutations.type";

const initialState = {
    // article: {
    //     author: {},
    //     title: "",
    //     description: "",
    //     body: "",
    //     tagList: []
    // },
    // comments: [],
    // username: [],
    // listLanguage: [],
    username:''
};

export const state = { ...initialState };

const getters = {
    // getListLanguage (state) {
    //     return state.listLanguage;
    // },
};

/* eslint no-param-reassign: ["error", { "props": false }] */
export const mutations = {
    // [SET_ARTICLE](state, article) {
    //     state.article = article;     
    // }

    // setListLanguage (state, listLanguage) {
    //     state.listLanguage = listLanguage
    // },
    // appendNewLanguage (state, language) {
    //     state.listLanguage.push(language)
    // },
    // updateLanguage (state, updatedLanguage) {
    //     let updatedLanguageIndex = state.listLanguage.findIndex(item => {
    //         return item.id == updatedLanguage.id
    //     })
    //     state.listLanguage.splice(updatedLanguageIndex, 1, updatedLanguage)
    // },
    // removeLanguage (state, languageId) {
    //     let deletedLanguageIndex = state.listLanguage.findIndex(item => {
    //         return item.id == languageId
    //     })
    //     state.listLanguage.splice(deletedLanguageIndex, 1)
    // }

    username(state,name){
        state.username = name;
      }
};

export const actions = {
    // async [FETCH_ARTICLE](context, articleSlug, prevArticle) {
    //     // avoid extronuous network call if article exists
    //     if (prevArticle !== undefined) {
    //         return context.commit(SET_ARTICLE, prevArticle);
    //     }
    //     const { data } = await ArticlesService.get(articleSlug);
    //     context.commit(SET_ARTICLE, data.article);
    //     return data;
    // },

    // async setListLanguage (context) {
    //     await axios.get('api/language')
    //     .then( response => {
    //         context.commit('setListLanguage', response.data)
    //         console.log(response)
    //     })
    //     .catch(function (error) {
    //         console.log(error);
    //     })
    // },

    // async createLanguage (context, language) {
    //     await axios.post('api/language', {
    //         name: language.name
    //     })
    //     .then( response => {
    //         context.commit('appendNewLanguage', response.data)
    //         console.log(response);
    //     })
    //     .catch(function (error) {
    //         console.log(error);
    //     })
    // },

    // async updateLanguage (context, language) {
    //     await axios.patch('api/language/' + language.id , {
    //         name: language.name
    //     })
    //     .then( response => {
    //         context.commit('updateLanguage', response.data)
    //         console.log(response);
    //     })
    //     .catch(function (error) {
    //         console.log(error);
    //     })
    // },

    // async deleteLanguage (context, language) {
    //     await axios.delete('api/language/' + language.id)
    //     .then( response => {
    //         context.commit('removeLanguage', language.id)
    //         console.log(response);
    //     })
    //     .catch(function (error) {
    //         console.log(error);
    //     })
    // },

    update_user_name(store,name){
        store.commit('username',name);
      }
};

export default {
    namespaced: true,

    state,
    getters,
    actions,
    mutations,
};


