package biz.myownradio.ff;

public enum Encoder {

    MP3("libmp3lame"),
    AAC("libfdk_aac");

    private String codecName;

    Encoder(String codecName) {
        this.codecName = codecName;
    }

    public String getEncoderName() {
        return codecName;
    }

}
