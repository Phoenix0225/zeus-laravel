import { createApp } from 'vue';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-Site-Id'] = 'HQ';

const app = createApp({
    data() {
        return {
            config: null,
            currentScreen: null,
            loadingScreen: false,
            loadingData: false,   // Nouvel état pour les données
            screenData: [],       // Les enregistrements de la base de données
            formData: {},
            submitting: false
        };
    },
    mounted() {
        axios.get('/api/ui/config')
            .then(response => {
                this.config = response.data;
            })
            .catch(error => console.error("Erreur UI Config", error));
    },
    methods: {
        loadScreen(screenId, menuLabel, resetForm = true) {
            if (!screenId) return;
            
            this.loadingScreen = true;
            this.currentScreen = null;
            this.screenData = []; 
            
            if (resetForm) {
                this.formData = {};
            }

            axios.get(`/api/ui/screens/${screenId}`)
                .then(response => {
                    this.currentScreen = response.data;
                    this.currentScreen.title = menuLabel;
                    
                    if (this.currentScreen.type === 'grid' && this.currentScreen.entity_code) {
                        this.fetchGridData(this.currentScreen.entity_code);
                    }
                })
                .catch(error => console.error("Erreur de chargement", error))
                .finally(() => {
                    this.loadingScreen = false;
                });
        },
        
        fetchGridData(entityCode) {
            this.loadingData = true;
            axios.get(`/api/dynamic/${entityCode}`)
                .then(response => {
                    // L'API dynamique renvoie la liste dans "data"
                    this.screenData = response.data.data || response.data; 
                })
                .catch(error => console.error("Erreur de chargement des données", error))
                .finally(() => {
                    this.loadingData = false;
                });
        },
        
        submitForm() {
            this.submitting = true;
            const isUpdate = !!this.formData.id;
            const method = isUpdate ? 'put' : 'post';
            const url = isUpdate 
                ? `/api/dynamic/${this.currentScreen.entity_code}/${this.formData.id}`
                : `/api/dynamic/${this.currentScreen.entity_code}`;

            axios[method](url, this.formData)
                .then(response => {
                    alert(`Enregistrement ${isUpdate ? 'modifié' : 'créé'} avec succès !`);
                    this.loadScreen(this.currentScreen.entity_code + '_grid', 'Gestion ' + this.currentScreen.entity_code);
                })
                .catch(error => {
                    console.error("Erreur de sauvegarde", error);
                    alert('Erreur lors de la sauvegarde.');
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        
        editRecord(entityCode, row) {
            // Clone l'objet pour éviter la modification réactive directe dans la grille
            this.formData = { ...row }; 
            this.loadScreen(entityCode + '_form', 'Édition : ' + entityCode, false);
        },
        
        deleteRecord(entityCode, id) {
            if (!confirm("🚨 Êtes-vous sûr de vouloir supprimer cet enregistrement ? Cette action est irréversible.")) return;
            
            axios.delete(`/api/dynamic/${entityCode}/${id}`)
                .then(() => {
                    this.fetchGridData(entityCode); // Recharge la grille dynamiquement
                })
                .catch(error => {
                    console.error("Erreur de suppression", error);
                    alert('Erreur lors de la suppression.');
                });
        }
    }
});

app.mount('#app');
