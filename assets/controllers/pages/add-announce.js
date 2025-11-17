$(document).ready(function(){

    let form = $('form[name=announces]');
    //if(form[0]) setFormHandlers();

});


function setFormHandlers(){
    let form = $('form[name=announces]');

    let select = form.find('.row-category')
    select.on('change', function(){
        let optS = $(this).find('option:selected');
        let value = optS.attr('value');
        if(value != '5'){
            form.find('.row-offer').remove();
            return;
        };

        let model = "<div class='form-row row row-offer block'>"+
            "<div class='form-label'>"+
                "Est-ce une offre d'emploi ou une demande ?"+
            "</div>"+
            "<div class='form-input input-field input-field col s12'>"+
                "<div><label><input type='radio' name='announces[offer]' required value='offer'><span></span> Offre</label></div>"+
                "<div><label><input type='radio' name='announces[offer]' required value='proposal'><span></span> Demande</label></div>"+
            "</div>"+
        "</div>";
        form.find('.row-category').after(model)

        
        
    })
}