
$(document).ready(function(){
    Dropzone.autoDiscover = false;
    $(".form_dropzone").dropzone({
        url: Routing.generate("miky_media_api_media_upload"),
        addRemoveLinks: true,
        autoDiscover: false,
        previewTemplate:  '<div class="dz-preview dz-file-preview"> <div class="dz-details"><img data-dz-thumbnail /> </div><div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div> <div class="dz-success-mark"><span>✔</span></div> <div class="dz-error-mark"><span>✘</span></div> <div class="dz-error-message"><span data-dz-errormessage></span></div> </div>',
        init: function () {
            var myDropzone = this;
            this.options.maxFiles = $(this.element).attr("data-media-max-files");
            $(this.element).find(".preview img").each(function(){
                var src = $(this).attr("src");
                var id = $(this).attr("data-media-id");
                var mockFile = { name: id, filename: id };
                myDropzone.emit("addedfile", mockFile);
                myDropzone.emit("thumbnail", mockFile, src);
                myDropzone.emit("complete", mockFile);
                // var existingFileCount = 1; // The number of files already uploaded
                // myDropzone.options.maxFiles = myDropzone.options.maxFiles - existingFileCount;
            });
            myDropzone.on("maxfilesexceeded", function(file) {
                myDropzone.removeFile(file);
            });
            this.on("success", function(file, serverResponse) {
                file.filename = serverResponse.id;
                file.name = serverResponse.id;
            });
            this.on("sending", function(file, xhr, formData){
                formData.append("media_token", $(this.element).attr("data-media-token"));
                formData.append("provider", $(this.element).attr("data-media-provider"));
                formData.append("context", $(this.element).attr("data-media-context"));
            });
            this.on("removedfile", function(file) {
                $.ajax({
                    url: Routing.generate("miky_media_api_media_remove", { id: file.filename }),
                    type: "POST",
                    data: { 'media_token': $(this).attr("data-media-token") }
                });
            });
        }
    });
});