import { config } from "dotenv";
import app from "./app";

config();

app(8080, "mor");
