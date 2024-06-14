$(document).ajaxStart(function () {
    console.log('Ajax Started!');
    $("#EOSLoader").show();
  });
  
$(document).ajaxStop(function () {
$("#EOSLoader").hide();
});

$(document).ready(function() {

    if($('#multiStepForm').length > 0) {

   setTimeout(() => {
    //GetCountries();
	   //GetWorkCountries();
    //GetExpList();
    //GetHighestEdu();
    //GetSector();
    //GetSkills();
    //GetLanguages();
    //forminatorInit();
    //hideBoxes();
   }, 100);

}


  

});


function forminatorInit() {

   

        $('#forminator-form-626__field--select-7').on('change', function(event){
      
            let countryId = $(this).val();
    
            GetState(countryId);
    
        });
    
        $('#forminator-form-626__field--select-11').on('change', function(){
    
            let stateId = $(this).val();
    
            GetCities(stateId);
        });
    
        $('#forminator-form-626__field--select-11').on('change', function(event){
          
            let stateId = $(this).val();
    
            GetCities(stateId);
        });
    
        //$('#forminator-module-626').submit(function(event) {
            //event.preventDefault();
        //})
		$(document).on('click', '#forminator-submit', function(event) {
			console.log('Really Here!!');
			//event.preventDefault();
		});
    
        $('#forminator-field-upload-2').change(function() {
            var file = this.files[0];
    
            var formData = new FormData();
            formData.append('file', file);
        
            $.ajax({
                url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
                type: 'POST',
                data: formData,
                processData: false, 
                contentType: false,
                success: function(response) {
                    
                },
                error: function(xhr, status, error) {
                    // Handle errors here
                    console.error(error);
                }
            });
        });
    

  
    
}
function GetWorkCountries(){
	$.ajax({
            url: myAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_country_background',
                post_id: 0,
                nonce: myAjax.nonce
            },
            success: function(response) {
                console.log(response);
                // Handle the response here
            },
            error: function(error) {
                console.log(error);
                // Handle the error here
            }
        });
}

function GetCountries() {

    let countryDDL = document.querySelector('#forminator-form-626__field--select-7');

    let otherCountries = document.querySelector('#checkbox-2 .forminator-field');

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetCountries',
        },
        success: function(response) {
            
            let res = JSON.parse(response);

            const countriesOptions = res.map(country => {
                return `<option value=${country.countryId}>${country.name}</option>`;
            })

            countriesOptions.forEach(item => {
                countryDDL.insertAdjacentHTML('beforeend', item);
            })

            const otherCountriesOptions = res.map(country => {
                return `<label for="forminator-field-checkbox-2-1-${country.countryId}" class="forminator-checkbox" title="${country.name}"><input type="checkbox" name="checkbox-2[]" value="${country.countryId}" id="forminator-field-checkbox-2-1-${country.countryId}" data-calculation="0"><span class="forminator-checkbox-box" aria-hidden="true"></span><span class="forminator-checkbox-label">${country.name}</span></label>`;
            })

            otherCountriesOptions.forEach(item => {
                //otherCountries.insertAdjacentHTML('beforeend', item);
            })


        }
    });

}

function GetState(countryId) {

    let stateDDL = document.querySelector('#forminator-form-626__field--select-11');

    stateDDL.innerHTML = '';

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetState',
            countryid: countryId
        },
        success: function(response) {

            let res = JSON.parse(response);

            const statesOptions = [];

            statesOptions.push('<option selected hidded disabled>Select State</option>');

            if(res.length <= 0 || res === '' || res.status === 400 ) { 
                stateDDL.insertAdjacentHTML('beforeend', '<option hidden disabled selected>No State Found</option>');
            } else {
                res.map(state => {
                    statesOptions.push(`<option value=${state.value}>${state.text}</option>`);
                })
            
                statesOptions.forEach(item => {
                    stateDDL.insertAdjacentHTML('beforeend', item);
                })
            }

        }
    });
}


function GetCities(stateId) {

    let cityDDL = document.querySelector('#forminator-form-626__field--select-8');

    cityDDL.innerHTML = '';

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetCities',
            stateId: stateId
        },
        success: function(response) {

            let res = JSON.parse(response);

            const citiesOptions = [];

            cityDDL.innerHTML = '';

            if(res === null || res === undefined || res.status === 400 ) {
                
                cityDDL.insertAdjacentHTML('beforeend', '<option hidden disabled selected>No City Found</option>');
            } else {

                citiesOptions.push('<option hidden disabled selected>Select City</option>')

                res.map(city => {
                    citiesOptions.push(`<option value=${city.value}>${city.text}</option>`);
                })
    
            
                citiesOptions.forEach(item => {
                    cityDDL.insertAdjacentHTML('beforeend', item);
                })
            }

            

            

        }
    });
}

function GetExpList() {

    let expList = document.querySelector('#forminator-form-626__field--select-9');

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetExpList',
        },
        success: function(response) {

            let res = JSON.parse(response);

            const expOptions = res.map(exp => {
                return `<option value=${exp.value}>${exp.text}</option>`;
            })

            

            expOptions.forEach(item => {
                expList.insertAdjacentHTML('beforeend', item);
            })

            

        }
    });
}

function GetHighestEdu() {

    let eduList = document.querySelector('.gc-select-education select');

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetEduList',
        },
        success: function(response) {

            let res = JSON.parse(response);

            const eduOptions = res.map(edu => {
                return `<option value=${edu.value}>${edu.text}</option>`;
            })

            eduOptions.forEach(item => {
                //eduList.insertAdjacentHTML('beforeend', item);
            })

            

        }
    });
}


function GetSector() {

    $.ajax({
            url: myAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_sector_background',
                post_id: 626,
                nonce: myAjax.nonce
            },
            success: function(response) {
                console.log(response);
                // Handle the response here
            },
            error: function(error) {
                console.log(error);
                // Handle the error here
            }
        });
}


function GetSkills() {

    let skillsList = document.querySelector('#checkbox-3 .forminator-field');

    $.ajax({
        type: 'POST',
        url: `/wp-content/plugins/cms-form-api/eos-fn.php`,
        data: {
            action: 'GetSkillList',
        },
        success: function(response) {

            let res = JSON.parse(response);

            const skills = res.map(skill => {
                return `<label for="forminator-field-checkbox-3-1-${skill.value}" class="forminator-checkbox" title="${skill.text}"><input type="checkbox" name="checkbox-3[]" value="${skill.value}" id="forminator-field-checkbox-3-1-${skill.value}" data-calculation="0"><span class="forminator-checkbox-box" aria-hidden="true"></span><span class="forminator-checkbox-label">${skill.text}</span></label>`;
            })

            skills.forEach(item => {
                skillsList.insertAdjacentHTML('beforeend', item);
            })

        }
    });
}


function GetLanguages() {

    $.ajax({
            url: myAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_language_background',
                post_id: 626,
                nonce: myAjax.nonce
            },
            success: function(response) {
                console.log(response);
                // Handle the response here
            },
            error: function(error) {
                console.log(error);
                // Handle the error here
            }
        });
}



function hideBoxes() {
    $('#checkbox-4').hide();
    $('#checkbox-2').hide();
}

