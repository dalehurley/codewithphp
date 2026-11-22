import { readFile } from "node:fs/promises";
import { createServer } from "node:http";

const fixturePath = new URL("./jobs.json", import.meta.url);
const allowedStates = new Set(["queued", "running", "failed"]);

createServer(async (request, response) => {
  function respond(status, body, headers = {}) {
    response.writeHead(status, { "Content-Type": "application/json", ...headers });
    response.end(request.method === "HEAD" ? undefined : JSON.stringify(body));
  }

  const path = request.url.split("?", 1)[0];
  const route = /^\/api\/jobs\/([a-z]+)$/.exec(path);
  if (!route) {
    respond(404, { error: "route_not_found" });
    return;
  }

  if (request.method !== "GET" && request.method !== "HEAD") {
    respond(405, { error: "method_not_allowed" }, { Allow: "GET, HEAD" });
    return;
  }

  const state = route[1];
  if (!allowedStates.has(state)) {
    respond(400, { error: "invalid_state" });
    return;
  }

  let jobs;
  try {
    const bytes = await readFile(fixturePath);
    const source = new TextDecoder("utf-8", { fatal: true, ignoreBOM: true }).decode(bytes);
    jobs = JSON.parse(source)?.[state];
    if (!Array.isArray(jobs)) {
      throw new Error("The requested state needs an array.");
    }
  } catch (error) {
    console.error("Could not load jobs.json:", error.message);
    respond(500, { error: "jobs_unavailable" });
    return;
  }

  respond(200, { state, jobs });
}).listen(3000, "127.0.0.1", () => {
  console.log("Job API listening on http://127.0.0.1:3000");
});
