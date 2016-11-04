package biz.myownradio.ff;

import biz.myownradio.tools.MORSettings;

import java.util.ArrayList;
import java.util.List;

abstract class Helper {

    static List<String> getFFmpegPrefix() {

        List<String> builder = new ArrayList<String>();

        if (MORSettings.getBooleanNow("command.ffmpeg.remote")) {
            builder.add("ssh");

            builder.add("-l");
            builder.add(MORSettings.getStringNow("command.ffmpeg.remote.user"));

            builder.add("-p");
            builder.add(MORSettings.getStringNow("command.ffmpeg.remote.port"));

            builder.add("-i");
            builder.add(MORSettings.getStringNow("command.ffmpeg.remote.identity"));

            builder.add(MORSettings.getStringNow("command.ffmpeg.remote.host"));
        }

        builder.add(MORSettings.getStringNow("command.ffmpeg"));

        return builder;

    }

}
