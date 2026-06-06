<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zeus ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased overflow-hidden">
    <div id="app" class="flex h-screen w-full">

        <aside class="w-64 bg-slate-900 text-white flex flex-col shadow-xl z-20">
            <div class="h-16 flex items-center justify-center border-b border-slate-800">
                <h1 class="text-2xl font-bold tracking-wider text-blue-400">⚡ ZEUS</h1>
            </div>
            <nav class="flex-1 p-4 overflow-y-auto">
                <ul v-if="config && config.menus">
                    <li v-for="menu in config.menus" :key="menu.id" class="mb-2">
                        <button @click="loadScreen(menu.screen_id, menu.label)" 
                                class="w-full flex items-center px-4 py-3 rounded-lg bg-slate-800 hover:bg-blue-600 transition-colors duration-200 text-left">
                            <span class="mr-3">✨</span> <span class="font-medium">@{{ menu.label }}</span>
                        </button>
                    </li>
                </ul>
                <div v-else class="text-slate-500 text-sm text-center mt-10">
                    Chargement du moteur...
                </div>
            </nav>
            <div class="p-4 border-t border-slate-800 text-xs text-slate-500 text-center">
                Connecté: HQ (Quartier Général)
            </div>
        </aside>

        <main class="flex-1 flex flex-col relative z-10">

            <header class="h-16 bg-white shadow flex items-center px-8 justify-between">
                <h2 class="text-xl font-semibold text-slate-800">
                    @{{ currentScreen ? currentScreen.title : 'Tableau de bord' }}
                </h2>
                <div class="flex items-center space-x-4">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">A</div>
                </div>
            </header>

            <div class="flex-1 p-8 overflow-auto bg-slate-50/50">

                <div v-if="loadingScreen" class="flex justify-center items-center h-64 text-blue-500 font-medium animate-pulse">
                    Interrogation du noyau Zeus...
                </div>

                <div v-else-if="currentScreen" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                    <div v-if="currentScreen.type === 'grid'">
                        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                            <h3 class="font-medium text-slate-700">Entité : @{{ currentScreen.entity_code }}</h3>
                            <button @click="loadScreen(currentScreen.entity_code + '_form', 'Nouveau ' + currentScreen.entity_code, true)" 
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                + Nouveau
                            </button>
                        </div>
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-slate-500 text-sm uppercase tracking-wider">
                                    <th v-for="col in currentScreen.config.columns" :key="col" class="px-6 py-4 border-b border-slate-200">
                                        @{{ col }}
                                    </th>
                                    <th class="px-6 py-4 border-b border-slate-200 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="loadingData">
                                    <td :colspan="currentScreen.config.columns.length + 1" class="px-6 py-8 text-center text-blue-500 animate-pulse">
                                        Récupération des enregistrements...
                                    </td>
                                </tr>

                                <tr v-else-if="screenData.length === 0">
                                    <td :colspan="currentScreen.config.columns.length + 1" class="px-6 py-8 text-center text-slate-400">
                                        Aucune donnée trouvée pour cette entité.
                                    </td>
                                </tr>

                                <tr v-else v-for="row in screenData" :key="row.id" class="hover:bg-slate-50 border-b border-slate-100 transition-colors">
                                    <td v-for="col in currentScreen.config.columns" :key="col" class="px-6 py-4 text-slate-700">
                                        @{{ row[col] }}
                                    </td>

                                    <td class="px-6 py-4 text-right space-x-3">
                                        <button @click="editRecord(currentScreen.entity_code, row)" 
                                                class="text-blue-500 hover:text-blue-700 font-medium text-sm transition-colors">
                                            Éditer
                                        </button>
                                        <button @click="deleteRecord(currentScreen.entity_code, row.id)" 
                                                class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else-if="currentScreen.type === 'form'" class="p-8">
                        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                            <h3 class="text-xl font-semibold text-slate-800 mb-6 border-b border-slate-100 pb-4">
                                Création : @{{ currentScreen.entity_code }}
                            </h3>

                            <form @submit.prevent="submitForm" class="space-y-6">
                                <div v-for="field in currentScreen.config.fields" :key="field.name">
                                    <label :for="field.name" class="block text-sm font-medium text-slate-700 mb-2">
                                        @{{ field.label }} <span v-if="field.required" class="text-red-500">*</span>
                                    </label>

                                    <input v-if="field.type === 'text' || field.type === 'email'" 
                                           :type="field.type" 
                                           :id="field.name" 
                                           v-model="formData[field.name]" 
                                           :required="field.required"
                                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" />
                                </div>

                                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-100">
                                    <button type="button" 
                                            @click="loadScreen(currentScreen.entity_code + '_grid', 'Gestion ' + currentScreen.entity_code)" 
                                            class="px-6 py-2 text-slate-600 font-medium hover:text-slate-800">
                                        Annuler
                                    </button>
                                    <button type="submit" 
                                            :disabled="submitting"
                                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                                        <span v-if="submitting">Sauvegarde...</span>
                                        <span v-else>Enregistrer</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div v-else class="p-8 text-center text-slate-500">
                        Type d'écran non supporté par le moteur de rendu : @{{ currentScreen.type }}
                    </div>

                </div>

                <div v-else class="flex flex-col items-center justify-center h-full text-slate-400">
                    <span class="text-6xl mb-4 opacity-50">⚡</span>
                    <p class="text-lg font-medium">Sélectionnez une application dans le menu.</p>
                </div>

            </div>
        </main>
    </div>
</body>
</html>
