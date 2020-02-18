<template>
    <div>
        <el-row :gutter="20">
	 		<el-col :span="24">
	 			<el-tabs tab-position="top" style="height: 200px;">
				    <el-tab-pane label="Links">
				    	<el-card class="box-card">
							<div slot="header" class="clearfix">
								<span>Listado de Links Cortos</span>
								<el-button type="success" @click="openAddLinkDialog()" style="float: right;">Agregar Link</el-button>
							</div>
					        <div class="table-responsive" v-if="links.length > 0">
		                      <table class="table table-sm" cellspacing="0" width="100%">
		                      		<colgroup>
								       <col span="1" style="width: 5%;">
								       <col span="1" style="width: 15%;">
								       <col span="1" style="width: 30%;">
								       <col span="1" style="width: 15%;">
								       <col span="1" style="width: 5%;">
									   <col span="1" style="width: 5%;">
								       <col span="1" style="width: 25%;">
								    </colgroup>
		                        	<thead>
			                          	<tr>
			                          		<th>Id</th>
				                            <th>Título del Link</th>
				                            <th>Campaña Asociada</th>
				                            <th>Url Larga</th>
				                            <th>Url Corta</th>
											<th>Cantidad de visitas</th>
				                            <th>Estado</th>
				                            <th>Acciones</th>
			                          	</tr>
		                        	</thead>
		                        	<tbody>
										<tr v-for="(link, index) in links">
											<td v-text="link.id"></td>
											<td v-text="link.link_name"></td>
											<td v-text="link.campaign.campaign_name"></td>
											<td style="font-size: 11px;">
											 	<a v-bind:href="link.link_long_url" target="_blank">{{ link.link_long_url }}</a>
											</td>
											<td style="font-size: 11px;">
												<button type="button" class="el-button el-button--default el-button--mini" style="padding: 6px 8px;">
													
													<a v-bind:href="link.short_code" target="_blank"><i class="el-icon-top-right"></i><span>{{ link.short_code }}</span></a>
												</button>
											  	
											  	<br>
											  	<el-button type="default" size="mini" icon="el-icon-document-copy" @click="copyToClipboard(link.short_code)" style="padding: 6px 8px;">Copiar al portapapeles</el-button>
											</td>
											<td v-text="link.link_visits.length"></td>
											<td v-text="link.status_name"></td>
											<td>
												<el-button-group>
													<el-button type="info" size="mini" icon="el-icon-edit" @click="openEditLinkDialog(link)" style="padding: 6px 8px;">Editar</el-button>
													<el-button type="danger" size="mini" icon="el-icon-delete" style="padding: 6px 8px;" @click="deleteLink(link, index)">Eliminar</el-button>
												</el-button-group>
											</td>
										</tr>
		                        	</tbody>
		                      	</table>
		                    </div>
							<div v-else>
								<i class="el-icon-loading"></i>
							</div>
						</el-card>

						<el-dialog title="Agregar Link" :visible.sync="dialogAddLinkData" @close="closeAddLinkDialog()">
							
							<el-form ref="addForm" label-width="120px">
								<el-row>
									<el-col :span="24">
										<el-form-item label="Campaña" id="campaign_id_add_form_item">
										    <el-select v-model="link.campaign_id" placeholder="Selecciona una campaña" required>
												<el-option v-for="campaign in campaigns" :label="campaign.campaign_name" :key="campaign.id" :value="campaign.id"></el-option>
										    </el-select>

										    <div id="campaign_id_add_form_error" class="el-form-item__error" style="display:none;">
									        	Debes seleccionar una campaña
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Título del Link" prop="link.link_name" id="link_name_add_form_item">
											<el-input placeholder="Escribe un título para el link..." v-model="link.link_name"></el-input>
											<div id="link_name_add_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar un título para el link
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Url Larga" prop="link.link_long_url" id="link_long_url_add_form_item">
											<el-input placeholder="Inserta el link que quieres acortar" v-model="link.link_long_url"></el-input>

											<div id="link_long_url_add_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar la url que quieres acortar
									        </div>
										</el-form-item>
									</el-col>
								</el-row>

								<el-form-item>
							    	<el-button type="primary" @click="addLink()">Crear</el-button>
							    	<el-button @click="closeAddLinkDialog()">Cancel</el-button>
							  	</el-form-item>
							</el-form>
						</el-dialog>

						<el-dialog title="Editar Link" :visible.sync="dialogEditLinkData" @close="closeEditLinkDialog()">
							<el-form ref="editForm" label-width="120px">
								<el-row>
									<el-col :span="24">
										<el-form-item label="Campaña" id="campaign_id_edit_form_item">
										    <el-select v-model="link_edit.campaign_id" placeholder="Selecciona una campaña" required>
												<el-option v-for="campaign in campaigns" :label="campaign.campaign_name" :key="campaign.id" :value="campaign.id" :selected="campaign.id === link_edit.campaign_id"></el-option>
										    </el-select>

										    <div id="campaign_id_edit_form_error" class="el-form-item__error" style="display:none;">
									        	Debes seleccionar una campaña
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Título del Link" prop="link_edit.link_name" id="link_name_edit_form_item">
											<el-input placeholder="Escribe un título para el link..." v-model="link_edit.link_name"></el-input>
											<div id="link_name_edit_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar un título para el link
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Url Larga" prop="link_edit.link_long_url" id="link_long_url_edit_form_item">
											<el-input placeholder="Copia la url del link que quieres acortar..." v-model="link_edit.link_long_url"></el-input>

											<div id="link_long_url_edit_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar la url que quieres acortar
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Estado">
											
											<select v-model="link_edit.status" class="form-control">
												<option v-for="status in status_options" :key="status.value" :value="status.value" v-text="status.label" ></option>
											</select>

										</el-form-item>
									</el-col>
								</el-row>

								<el-form-item>
							    	<el-button type="primary" @click="editLink()">Editar</el-button>
							    	<el-button @click="closeEditLinkDialog()">Cancel</el-button>
							  	</el-form-item>
							</el-form>
						</el-dialog>
				    </el-tab-pane>
				    <el-tab-pane label="Campañas">
				    	<el-card class="box-card">
							<div slot="header" class="clearfix">
								<span>Listado de Campañas</span>
								<el-button type="success" @click="openAddCampaignDialog()" style="float: right;">Agregar Campaña</el-button>
							</div>
					        <div class="table-responsive" v-if="campaigns.length > 0">
		                      <table class="table table-sm" cellspacing="0" width="100%">
		                      		<colgroup>
								       <col span="1" style="width: 5%;">
								       <col span="1" style="width: 10%;">
								       <col span="1" style="width: 10%;">
								       <col span="1" style="width: 5%;">
								       <col span="1" style="width: 5%;">
								       <col span="1" style="width: 60%;">
								    </colgroup>
		                        	<thead>
			                          	<tr>
			                          		<th>Id</th>
				                            <th>Nombre de la Campaña</th>
				                            <th>Cantidad de Links</th>
				                            <th>Estado</th>
				                            <th>Acciones</th>
			                          	</tr>
		                        	</thead>
		                        	<tbody>
										<tr v-for="(campaign, index) in campaigns">
											<td v-text="campaign.id"></td>
											<td v-text="campaign.campaign_name"></td>
											<td v-text="campaign.links_count"></td>
											<td v-text="campaign.status_name"></td>
											<td>
												<el-button-group>
													<el-button type="info" size="mini" icon="el-icon-edit" @click="openEditCampaignDialog(campaign)" style="padding: 6px 8px;">Editar</el-button>
													<el-button type="danger" size="mini" icon="el-icon-delete" style="padding: 6px 8px;" @click="deleteCampaign(campaign, index)">Eliminar</el-button>
												</el-button-group>
											</td>
										</tr>
		                        	</tbody>
		                      	</table>
		                    </div>
							<div v-else>
								<i class="el-icon-loading"></i>
							</div>
						</el-card>

						<el-dialog title="Agregar Campaña" :visible.sync="dialogAddCampaignData" @close="closeAddCampaignDialog()">
							<el-form ref="addCampaignForm" label-width="120px">
								<el-row>
									<el-col :span="24">
										<el-form-item label="Nombre de la Campaña" prop="campaign.campaign_name" id="campaign_name_add_form_item">
											<el-input placeholder="Escribe un nombre para la campaña" v-model="campaign.campaign_name" required></el-input>
											<div id="campaign_name_add_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar un nombre para la campaña
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Descripción de la Campaña" prop="campaign.campaign_description">
											<el-input type="textarea" v-model="campaign.campaign_description"></el-input>
										</el-form-item>
									</el-col>
								</el-row>

								<el-form-item>
							    	<el-button type="primary" @click="addCampaign()">Crear</el-button>
							    	<el-button @click="closeAddCampaignDialog()">Cancelar</el-button>
							  	</el-form-item>
							</el-form>
						</el-dialog>

						<el-dialog title="Editar Campaña" :visible.sync="dialogEditCampaignData" @close="closeEditCampaignDialog()">
							<el-form ref="editCampaignForm" label-width="120px">
								<el-row>
									<el-col :span="24">
										<el-form-item label="Nombre de la Campaña" prop="campaign_edit.campaign_name" id="campaign_name_edit_form_item">
											<el-input placeholder="Escribe un nombre para la campaña" v-model="campaign_edit.campaign_name" required></el-input>
											<div id="campaign_name_edit_form_error" class="el-form-item__error" style="display:none;">
									        	Debes ingresar un nombre para la campaña
									        </div>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Descripción de la Campaña" prop="campaign_edit.campaign_description">
											<el-input type="textarea" v-model="campaign_edit.campaign_description"></el-input>
										</el-form-item>
									</el-col>
									<el-col :span="24">
										<el-form-item label="Estado">
											<select v-model="campaign_edit.status" class="form-control">
												<option v-for="status in status_options" :key="status.value" :value="status.value" v-text="status.label" ></option>
											</select>
										</el-form-item>
									</el-col>
								</el-row>
								<el-form-item>
							    	<el-button type="primary" @click="editCampaign()">Editar</el-button>
							    	<el-button @click="closeEditCampaignDialog()">Cancelar</el-button>
							  	</el-form-item>
							</el-form>
						</el-dialog>
				    </el-tab-pane>
				</el-tabs>
	 		</el-col>
		</el-row>
    </div>
</template>

<script>
export default {
  data() {
    return {
      	links: [],
      	campaigns: [],
      	link: {campaign_id: null, link_name: '', link_long_url: ''},
      	link_edit: {campaign_id: null, link_name: '', link_long_url: ''},
		campaign: {campaign_name: '', campaign_description: ''},
		campaign_edit: {campaign_name: '', campaign_description: ''},
      	dialogAddLinkData: false,
      	dialogEditLinkData: false,
		dialogAddCampaignData: false,
		dialogEditCampaignData: false,
      	validHostnameRegex: '^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$',
		status_options: [
			{
				label: 'Activo',
				value: 1
			},
			{
				label: 'Desactivado',
				value: 0
			}
		]
    }
  },
  created(){
	this.getLinks();
    this.getCampaigns();
  },
  methods:{
    addLink(){

    	this.clearAddFormErrors();

    	var validForm = true;
    	var regex = new RegExp(this.validHostnameRegex);

    	if(this.link.link_name.length == 0){
    		validForm = false;
    		$('#link_name_add_form_item').addClass('is-error');
    		$('#link_name_add_form_error').show();
    	}

    	if(!this.link.campaign_id){
    		validForm = false;
    		$('#campaign_id_add_form_item').addClass('is-error');
    		$('#campaign_id_add_form_error').show();
    	}

    	if(this.link.link_long_url.length == 0){
    		validForm = false;

    		$('#link_long_url_add_form_item').addClass('is-error');
    		$('#link_long_url_add_form_error').show();
    	}
    	else{

    		if(regex.test(this.link.link_long_url) == true){
    			validForm = false;

    			$('#link_long_url_add_form_item').addClass('is-error');
    			$('#link_long_url_add_form_error').show();
				$('#link_long_url_add_form_error').text('Debes incluir el protocolo http:// o https://');
    		}
    	}

    	if(validForm){
    		const newLink = {
	        	link_name: this.link.link_name, 
	        	campaign_id: this.link.campaign_id, 
	       		link_long_url: this.link.link_long_url, 
	      	};


	      	axios.post(webroot+'/api/links', newLink)
	        	.then((res) =>{
	          		const linkServer = res.data.data;

	          		const createdLink = {
	          			id: linkServer.id,
	          			campaign_id: linkServer.campaign_id,
	          			link_name: linkServer.link_name,
	          			link_long_url: linkServer.link_long_url,
	          			short_code: process.env.MIX_SHORT_DNS+'/'+linkServer.short_code,
	          			status_name: (linkServer.status == 1) ? 'Activo' : 'Desactivado',
	          			status: linkServer.status,
	          			created_at: linkServer.created_at,
	          			updated_at: linkServer.updated_at,
	          			campaign: linkServer.campaign,
						link_visits: linkServer.link_visits
	          		};

	          		//console.log(createdLink);
	          		
			        this.link = {campaign_id: null, link_name: '', link_long_url: ''};
			        this.links.unshift(createdLink);
			        this.getCampaigns();
			       	this.dialogAddLinkData = false;

		            this.$notify({
		            	title: 'Éxito!',
		                message: 'Link agregado al sistema.',
		                type: 'success'
		            });
	        });
    	}
    	else{
    		return;
    	}
    },
    editLink(){

    	this.clearEditFormErrors();

    	var validForm = true;
    	var regex = new RegExp(this.validHostnameRegex);

    	if(this.link_edit.link_name.length == 0){
    		validForm = false;
    		$('#link_name_edit_form_item').addClass('is-error');
    		$('#link_name_edit_form_error').show();
    	}

    	if(!this.link_edit.campaign_id){
    		validForm = false;
    		$('#campaign_id_edit_form_item').addClass('is-error');
    		$('#campaign_id_edit_form_error').show();
    	}

    	if(this.link_edit.link_long_url.length == 0){
    		validForm = false;

    		$('#link_long_url_edit_form_item').addClass('is-error');
    		$('#link_long_url_edit_form_error').show();
    	}
    	else{

    		if(regex.test(this.link_edit.link_long_url) == true){
    			validForm = false;

    			$('#link_long_url_edit_form_item').addClass('is-error');
    			$('#link_long_url_edit_form_error').show();
				$('#link_long_url_edit_form_error').text('Debes incluir el protocolo http:// o https://');
    		}
    	}

    	if(validForm){
    		axios.put(webroot+'/api/links/'+this.link_edit.id, this.link_edit)
	        .then(res=>{

	          	const linkServer = res.data.data;
	          	const index = this.links.findIndex(item => item.id === this.link_edit.id);

	          	const updatedLink = {
	          		id: linkServer.id,
	          		campaign_id: linkServer.campaign_id,
	          		link_name: linkServer.link_name, 
	          		link_long_url: linkServer.link_long_url,
	          		short_code: process.env.MIX_SHORT_DNS+'/'+linkServer.short_code,
	          		status_name: (linkServer.status == 1) ? 'Activo' : 'Desactivado',
	          		status: linkServer.status,
		          	created_at: linkServer.created_at,
		          	updated_at: linkServer.updated_at,
		          	campaign: linkServer.campaign,
					link_visits: linkServer.link_visits
	          	};

	        	this.links[index] = updatedLink;
	        	this.link_edit = {campaign_id: null, link_name: '', link_long_url: ''};
	        	this.getCampaigns();
		       	this.dialogEditLinkData = false;

	            this.$notify({
	            	title: 'Éxito!',
	                message: 'Link actualizado en el sistema.',
	                type: 'success'
	            });

	        });
    	}
    	else{
    		return;
    	}
    },
    deleteLink(link, index){

    	this.$confirm('Este link se borrará de forma permanente junto con todo su registro de visitas. Estas seguro?', 'Alerta', {
          	confirmButtonText: 'Eliminar',
          	cancelButtonText: 'Cancelar',
          	type: 'danger'
        }).then(() => {


	        axios.delete(webroot+`/api/links/${link.id}`)
	        .then(()=>{
	        	this.getCampaigns()
	        	this.links.splice(index, 1);
	        });

          	this.$notify({
            	title: 'Éxito!',
                message: 'Link eliminado en el sistema.',
                type: 'success'
            });
        }).catch(() => {
          	this.$notify({
            	title: 'Información',
                message: 'Eliminación cancelada',
                type: 'info'
            });   
        });
    },
	addCampaign(){
		this.clearAddCampaignFormErrors();

    	var validForm = true;

    	if(this.campaign.campaign_name.length == 0){
    		validForm = false;
    		$('#campaign_name_add_form_item').addClass('is-error');
    		$('#campaign_name_add_form_error').show();
    	}

    	if(validForm){
    		const newCampaign = {
	        	campaign_name: this.campaign.campaign_name, 
	        	campaign_description: this.campaign.campaign_description
	      	};


	      	axios.post(webroot+'/api/campaigns', newCampaign)
	        	.then((res) =>{
	          		const campaignServer = res.data.data;

	          		const createdCampaign = {
	          			id: campaignServer.id,
	          			campaign_name: campaignServer.campaign_name,
	          			campaign_description: campaignServer.campaign_description,
	          			status_name: (campaignServer.status == 1) ? 'Activo' : 'Desactivado',
	          			status: campaignServer.status,
	          			created_at: campaignServer.created_at,
	          			updated_at: campaignServer.updated_at,
	          			links_count: campaignServer.links.length
	          		};

	          		console.log(createdCampaign);
	          		
			        this.campaign = {campaign_name: '', campaign_description: ''};
			        this.campaigns.unshift(createdCampaign);
			        //this.getCampaigns();
			       	this.dialogAddCampaignData = false;

		            this.$notify({
		            	title: 'Éxito!',
		                message: 'Campaña agregada al sistema.',
		                type: 'success'
		            });
	        });
    	}
    	else{
    		return;
    	}
	},
	editCampaign(){

		this.clearEditCampaignFormErrors();

    	var validForm = true;

    	if(this.campaign_edit.campaign_name.length == 0){
    		validForm = false;
    		$('#campaign_name_edit_form_item').addClass('is-error');
    		$('#campaign_name_edit_form_error').show();
    	}

    	if(validForm){
    		axios.put(webroot+'/api/campaigns/'+this.campaign_edit.id, this.campaign_edit)
	        .then(res=>{

	          	const campaignServer = res.data.data;
	          	const index = this.campaigns.findIndex(item => item.id === this.campaign_edit.id);

				const updatedCampaign = {
					id: campaignServer.id,
					campaign_name: campaignServer.campaign_name,
					campaign_description: campaignServer.campaign_description,
					status_name: (campaignServer.status == 1) ? 'Activo' : 'Desactivado',
					status: campaignServer.status,
					created_at: campaignServer.created_at,
					updated_at: campaignServer.updated_at,
					links_count: campaignServer.links.length
				};

	        	this.campaigns[index] = updatedCampaign;
	        	this.campaign_edit = {campaign_name: '', campaign_description: ''};
				this.getLinks();
		       	this.dialogEditCampaignData = false;

	            this.$notify({
	            	title: 'Éxito!',
	                message: 'Campaña actualizada en el sistema.',
	                type: 'success'
	            });
	        });
    	}
    	else{
    		return;
    	}
	},
	deleteCampaign(campaign, index){

    	this.$confirm('Este campaña se borrará de forma permanente junto con todo sus links asociados y los registro de visitas. Estas seguro?', 'Alerta', {
          	confirmButtonText: 'Eliminar',
          	cancelButtonText: 'Cancelar',
          	type: 'danger'
        }).then(() => {

	        axios.delete(webroot+`/api/campaigns/${campaign.id}`)
	        .then(()=>{
	        	this.campaigns.splice(index, 1);
				this.getLinks();
	        });

          	this.$notify({
            	title: 'Éxito!',
                message: 'Campaña eliminada en el sistema.',
                type: 'success'
            });
        }).catch(() => {
          	this.$notify({
            	title: 'Información',
                message: 'Eliminación cancelada',
                type: 'info'
            });   
        });
    },
    openAddLinkDialog(){
      	this.dialogAddLinkData = true;
    },
    closeAddLinkDialog(){
      	this.link = {campaign_id: null, link_name: '', link_long_url: ''};
      	this.dialogAddLinkData = false;
      	this.clearAddFormErrors();
    },
    openEditLinkDialog(link){

      	this.link_edit.id = link.id;
      	this.link_edit.campaign_id = link.campaign_id;
      	this.link_edit.link_name = link.link_name;
      	this.link_edit.link_long_url = link.link_long_url;
      	this.link_edit.status = link.status;

      	this.dialogEditLinkData = true;
    },
    closeEditLinkDialog(){
    	this.link_edit = {campaign_id: null, link_name: '', link_long_url: ''};
    	this.dialogEditLinkData = false;
    	this.clearEditFormErrors();
    },
    clearAddFormErrors(){
    	$('#link_name_add_form_item').removeClass('is-error');
    	$('#link_name_add_form_error').hide();

    	$('#campaign_id_add_form_item').removeClass('is-error');
    	$('#campaign_id_add_form_error').hide();

    	$('#link_long_url_add_form_item').removeClass('is-error');
    	$('#link_long_url_add_form_error').hide();
    	$('#link_long_url_add_form_error').text('Debes ingresar la url que quieres acortar');
    },
    clearEditFormErrors(){
    	$('#link_name_edit_form_item').removeClass('is-error');
    	$('#link_name_edit_form_error').hide();

    	$('#campaign_id_edit_form_item').removeClass('is-error');
    	$('#campaign_id_edit_form_error').hide();

    	$('#link_long_url_edit_form_item').removeClass('is-error');
    	$('#link_long_url_edit_form_error').hide();
    	$('#link_long_url_edit_form_error').text('Debes ingresar la url que quieres acortar');
    },
	openAddCampaignDialog(){
		this.dialogAddCampaignData = true;
	},
	closeAddCampaignDialog(){
      	this.campaign = {campaign_name: '', campaign_description: ''};
      	this.dialogAddCampaignData = false;
      	this.clearAddCampaignFormErrors();
    },
	clearAddCampaignFormErrors(){
    	$('#campaign_name_add_form_item').removeClass('is-error');
    	$('#campaign_name_add_form_error').hide();
    },
	openEditCampaignDialog(campaign){

		this.campaign_edit.id = campaign.id;
      	this.campaign_edit.campaign_name = campaign.campaign_name;
      	this.campaign_edit.campaign_description = campaign.campaign_description;
      	this.campaign_edit.status = campaign.status;

		this.dialogEditCampaignData = true;
	},
	closeEditCampaignDialog(){
      	this.campaign_edit = {campaign_name: '', campaign_description: ''};
      	this.dialogEditCampaignData = false;
      	this.clearEditCampaignFormErrors();
    },
	clearEditCampaignFormErrors(){
    	$('#campaign_name_edit_form_item').removeClass('is-error');
    	$('#campaign_name_edit_form_error').hide();
    },
    getCampaigns(){
    	axios.get(webroot+'/api/campaigns').then(res=>{
	      	var campaigns_bckp = res.data;

	      	if(campaigns_bckp.length > 0){
	      		var campaigns_new = [];

		      	campaigns_bckp.forEach(function(value, index, array){
		            campaigns_new[index] = value;
		            campaigns_new[index]['links_count'] = value.links.length;
		            campaigns_new[index]['status_name'] = (value.status == 1) ? 'Activo' : 'Desactivado';
		        });

		        this.campaigns = campaigns_new;
	      	}
	    });
    },
	getLinks(){
		axios.get(webroot+'/api/links').then(res=>{
			var links_bckp = res.data;

			if(links_bckp.length > 0){
				var links_new = [];

				links_bckp.forEach(function(value, index, array){
					links_new[index] = value;
					links_new[index]['short_code'] = process.env.MIX_SHORT_DNS+'/'+value.short_code;
					links_new[index]['status_name'] = (value.status == 1) ? 'Activo' : 'Desactivado';
				});

				this.links = links_new;
			}
		});
	},
    copyToClipboard(link_url) {
		var el = document.createElement('textarea');
	   	el.value = link_url;
	   	document.body.appendChild(el);
	   	el.select();
	    document.execCommand('copy');
	    document.body.removeChild(el);

        this.$message('Link corto copiado al portapapeles.');
    }
  }
}
</script>
