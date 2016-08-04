package biz.myownradio.tools;

/**
 * Created by Roman on 26.12.2014.
 */
public class ByteTools {

    public static byte[] longToBytes(long value) {

        byte[] temp = new byte[Long.BYTES];

        for (int i = 0; i < temp.length; i ++) {
            temp[temp.length - i - 1] = (byte) (value >> (i * Byte.SIZE));
        }

        return temp;

    }

    public static long bytesToLong(byte[] bytes) {

        return bytesToLong(bytes, 0, Long.BYTES);

    }

    public static long bytesToLong(byte[] b, int pos, int len) {

        if (len > Long.BYTES) {
            throw new IllegalArgumentException("Array is too long");
        }

        long temp = 0L;

        for (int i = 0; i < len; i ++) {
            temp <<= Byte.SIZE;
            temp += b[pos + i] & 0xFF ;
        }

        return  temp;

    }

}
