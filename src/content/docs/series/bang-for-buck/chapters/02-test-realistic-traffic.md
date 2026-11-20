---
title: "02: Test Steady Traffic, Bursts and Cold Starts"
description: "Use k6 arrival rates, bursts and a PHP-FPM restart to find what the Laravel shop can sustain and how it recovers."
series: "bang-for-buck"
chapter: 2
difficulty: "Intermediate"
estimatedTime: "PT25M"
sidebar:
  order: 2
---

## Overview

A steady run tells you what happened under one load. A burst tests whether the application can absorb a sudden increase and clear its backlog. A restart shows how the first requests behave while PHP rebuilds its opcode cache. Run these as separate experiments so you can tell which condition produced each result.

## Prerequisites

Complete [Set a Baseline for a Laravel App](/series/bang-for-buck/chapters/01-set-a-baseline/). Keep the Compose stack running and work in `code/bang-for-buck`. Run one benchmark at a time so the harnesses do not compete for the same resources. Allow 25 minutes.

## Step 1 – Compare fixed demand with a fixed number of users

The steady profile uses an open model. k6 attempts to start iterations at the specified rate even when a response slows down. A closed-model test keeps a fixed pool of virtual users. Each user waits for a workflow to finish and pauses briefly before starting another. When workflows take longer, the closed test may issue less traffic. [k6 open and closed models](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/open-vs-closed/)

```bash
RATE=20 DURATION_SECONDS=30 bash bench/run.sh steady
VUS=4 DURATION_SECONDS=30 bash bench/run.sh closed
```

Compare the actual started and successful workflow counts. The closed profile is a useful model for a known number of people performing sequential actions. Use the open profile when you need to compare two versions at the same incoming demand.

If a run reports dropped iterations, k6 could not start all the scheduled work. Check virtual-user limits and whether the load generator had CPU to spare. Also check how long the application held each virtual user, especially during export polling. Compare outcomes only after accounting for the actual load each run delivered. [k6 dropped iterations](https://grafana.com/docs/k6/latest/using-k6/scenarios/concepts/dropped-iterations/)

In a local overload check with a deliberately low cap of 16 virtual users, 736 workflows completed and 4,065 scheduled iterations were dropped. The reported catalogue and order p95 values still met their targets. The dropped-iteration gate failed because those latencies describe the work k6 managed to start.

## Step 2 – Replay a burst and watch recovery

```bash
RATE=20 bash bench/run.sh burst
```

The scheduled window is 60 seconds. Demand holds at 20 workflows per second for 15 seconds, ramps to 60 over five seconds, holds there for 15 seconds, ramps back over five seconds and stays at 20 for the final 20 seconds. The run allows in-flight exports to finish after the arrival window.

In `summary.json`, compare `shop_catalogue_ms{phase:base}`, `shop_catalogue_ms{phase:peak}` and `shop_catalogue_ms{phase:recovery}`. Each phase tags a workflow when it starts. The overall thresholds still apply to the whole run. A passing overall p95 can hide a slow peak, so inspect those phase figures before calling it a clean recovery. Check `database.tsv` for pending jobs and the oldest job available to run. If the queue is still growing after arrivals fall, capacity has not caught up. [k6 ramping arrival rate executor](https://grafana.com/docs/k6/latest/using-k6/scenarios/executors/ramping-arrival-rate/)

## Step 3 – Measure the first request after a PHP restart

```bash
bash bench/run.sh cold
```

This profile restarts PHP-FPM and waits for its socket. It sends two identical catalogue requests before making any PHP health request. The report prints the first and second response times. PostgreSQL and the queue worker remain up. This experiment isolates a PHP restart on the running lab, with two individual observations. Repeat the restart and compare several pairs before drawing a conclusion. [PHP OPcache](https://www.php.net/manual/en/intro.opcache.php)

## Exercise

Increase the steady arrival rate in small steps. Keep the duration, seed, container limits and tool versions fixed. At each rate, record the catalogue and order p95, export completion, failures, drops and peak worker or database use. Stop at the first rate that misses a target. Report the highest rate that passes as the highest **tested** rate under these conditions. If every tested rate passes, you have a lower bound on capacity.

The harness and shop share a Docker engine in this lab. Check the load generator separately when drawing a capacity conclusion from higher loads. For a before-and-after comparison, use comparable starting data and alternate run order. Stop idle containers with `docker compose down` when you finish. Compose keeps the lab’s named data volumes for the next session.

## What to keep for the next chapter

Save the result directories for your usual load, your first failed rate and the burst. Note which metric moved first. A full PHP worker pool, a climbing database queue and a slow external call point to different changes.

[Profile a Slow Laravel Request](/series/bang-for-buck/chapters/03-profile-a-slow-request/)
