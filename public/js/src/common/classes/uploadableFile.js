export class UploadableFile {
    constructor (file) {
        // use for uploading
        this.file = file;
        this.id = `${file.name}-${file.size}-${file.lastModified}-${file.type}`;
        this.isImage = file.type.match(/image.*/);
        // use for the image preview
        if (this.isImage) {
            this.url = URL.createObjectURL(file);
        }
        this.status = null;
    }
}
