--
-- PostgreSQL database dump
--

\restrict taIhwPC8FXPYdDfe767Tiefqr6GXh4w2eFuCZRCFcQtGeowghRXH4fesv0MvjuE

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2026-09-12 22:22:30

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 28406)
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- TOC entry 5613 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 912 (class 1247 OID 28418)
-- Name: conversation_scope; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.conversation_scope AS ENUM (
    'workspace',
    'company'
);


ALTER TYPE public.conversation_scope OWNER TO postgres;

--
-- TOC entry 915 (class 1247 OID 28424)
-- Name: payment_method_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.payment_method_enum AS ENUM (
    'midtrans',
    'manual'
);


ALTER TYPE public.payment_method_enum OWNER TO postgres;

--
-- TOC entry 279 (class 1255 OID 28429)
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 220 (class 1259 OID 28430)
-- Name: addons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.addons (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    addon_name character varying(100) NOT NULL,
    price_per_user numeric(12,2) NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.addons OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 48588)
-- Name: ai_processing_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ai_processing_logs (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    workspace_id uuid,
    project_name character varying(255) NOT NULL,
    payload json NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ai_processing_logs OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 28441)
-- Name: announcement_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcement_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid,
    user_id uuid
);


ALTER TABLE public.announcement_recipients OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 28446)
-- Name: announcements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcements (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    due_date date,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    auto_due date,
    company_id uuid
);


ALTER TABLE public.announcements OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 28457)
-- Name: attachments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attachments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    attachable_type character varying(100),
    attachable_id uuid,
    file_url text NOT NULL,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_size bigint,
    file_type character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.attachments OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 28468)
-- Name: board_columns; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.board_columns (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    "position" integer,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.board_columns OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 28476)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 28484)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 28492)
-- Name: calendar_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_events (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    start_datetime timestamp without time zone,
    end_datetime timestamp without time zone,
    recurrence character varying(100),
    is_private boolean DEFAULT false,
    is_online_meeting boolean DEFAULT false,
    meeting_link text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    company_id uuid,
    location character varying(255)
);


ALTER TABLE public.calendar_events OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 28504)
-- Name: calendar_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    event_id uuid,
    user_id uuid,
    status character varying(50),
    attendance boolean DEFAULT false
);


ALTER TABLE public.calendar_participants OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 28510)
-- Name: checklists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.checklists (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    title character varying(255) NOT NULL,
    is_done boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    "position" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.checklists OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 28521)
-- Name: colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.colors (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rgb character varying(20) NOT NULL
);


ALTER TABLE public.colors OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 28527)
-- Name: comments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    parent_comment_id uuid,
    commentable_type character varying(100),
    commentable_id uuid,
    user_id uuid,
    content text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.comments OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 28537)
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    address text,
    phone character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    trial_start timestamp(0) without time zone,
    trial_end timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    CONSTRAINT companies_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text])))
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 28550)
-- Name: conversation_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversation_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    user_id uuid,
    joined_at timestamp without time zone DEFAULT now(),
    is_admin boolean DEFAULT false,
    last_read_at timestamp without time zone
);


ALTER TABLE public.conversation_participants OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 28557)
-- Name: conversations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    type character varying(50) DEFAULT 'group'::character varying,
    name character varying(255),
    created_by uuid,
    updated_at timestamp without time zone,
    last_message_id uuid,
    scope public.conversation_scope DEFAULT 'workspace'::public.conversation_scope NOT NULL,
    company_id uuid
);


ALTER TABLE public.conversations OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 37523)
-- Name: decisions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.decisions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    decision_date date NOT NULL,
    evidence_file_id uuid,
    is_validated boolean DEFAULT false,
    validated_by uuid,
    validated_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.decisions OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 28566)
-- Name: document_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.document_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    document_id uuid NOT NULL,
    user_id uuid NOT NULL,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.document_recipients OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 28575)
-- Name: feedbacks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.feedbacks (
    id bigint NOT NULL,
    name character varying(255),
    email character varying(255),
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.feedbacks OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 28584)
-- Name: feedbacks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.feedbacks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.feedbacks_id_seq OWNER TO postgres;

--
-- TOC entry 5614 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedbacks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.feedbacks_id_seq OWNED BY public.feedbacks.id;


--
-- TOC entry 238 (class 1259 OID 28585)
-- Name: files; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.files (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    folder_id uuid,
    workspace_id uuid,
    file_url text NOT NULL,
    is_private boolean DEFAULT false,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_path character varying(255),
    file_size integer,
    file_type character varying(255),
    company_id uuid
);


ALTER TABLE public.files OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 28595)
-- Name: folders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.folders (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    is_private boolean DEFAULT false,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    parent_id uuid,
    company_id uuid
);


ALTER TABLE public.folders OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 28604)
-- Name: insight_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insight_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    insight_id uuid,
    user_id uuid
);


ALTER TABLE public.insight_recipients OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 28609)
-- Name: insights; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insights (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    description text,
    delivery_days character varying(50),
    delivery_time time without time zone,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.insights OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 28619)
-- Name: invitations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invitations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email_target character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    invited_by uuid,
    company_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    expired_at timestamp without time zone,
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.invitations OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 28631)
-- Name: labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.labels (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    color_id uuid,
    created_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL,
    workspace_id uuid NOT NULL
);


ALTER TABLE public.labels OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 28641)
-- Name: leave_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leave_requests (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    leave_type character varying(100),
    start_date date,
    end_date date,
    reason text,
    status character varying(50) DEFAULT 'pending'::character varying,
    approved_by uuid,
    attachment_url text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.leave_requests OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 28651)
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    sender_id uuid,
    content text,
    message_type character varying(50),
    reply_to_message_id uuid,
    is_edited boolean DEFAULT false,
    edited_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    read_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 28661)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 28667)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 5615 (class 0 OID 0)
-- Dependencies: 247
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 248 (class 1259 OID 28668)
-- Name: mindmap_nodes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmap_nodes (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    mindmap_id uuid NOT NULL,
    parent_id uuid,
    title character varying(255) NOT NULL,
    description text,
    type character varying(50) DEFAULT 'default'::character varying,
    x_position numeric(10,2) DEFAULT 0,
    y_position numeric(10,2) DEFAULT 0,
    connection_side character varying(20) DEFAULT 'auto'::character varying,
    sort_order integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmap_nodes OWNER TO postgres;

--
-- TOC entry 5616 (class 0 OID 0)
-- Dependencies: 248
-- Name: TABLE mindmap_nodes; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmap_nodes IS 'Tabel untuk menyimpan node-node dalam mind map';


--
-- TOC entry 249 (class 1259 OID 28684)
-- Name: mindmaps; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmaps (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid NOT NULL,
    title character varying(255) DEFAULT 'Mind Map Utama'::character varying NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmaps OWNER TO postgres;

--
-- TOC entry 5617 (class 0 OID 0)
-- Dependencies: 249
-- Name: TABLE mindmaps; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmaps IS 'Tabel untuk menyimpan mind map dalam workspace';


--
-- TOC entry 250 (class 1259 OID 28696)
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    company_id uuid NOT NULL,
    workspace_id uuid,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    context character varying(255),
    notifiable_type character varying(255) NOT NULL,
    notifiable_id uuid NOT NULL,
    actor_id uuid,
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp(0) without time zone,
    action_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifications_type_check CHECK (((type)::text = ANY (ARRAY[('chat'::character varying)::text, ('task'::character varying)::text, ('announcement'::character varying)::text, ('schedule'::character varying)::text])))
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 28712)
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.otp_verifications (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    otp character varying(6) NOT NULL,
    type character varying(20) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    is_used boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT otp_verifications_type_check CHECK (((type)::text = ANY (ARRAY[('register'::character varying)::text, ('reset_password'::character varying)::text])))
);


ALTER TABLE public.otp_verifications OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 28723)
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.otp_verifications_id_seq OWNER TO postgres;

--
-- TOC entry 5618 (class 0 OID 0)
-- Dependencies: 252
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- TOC entry 253 (class 1259 OID 28724)
-- Name: plans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.plans (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    plan_name character varying(100) NOT NULL,
    price_monthly numeric(12,2) NOT NULL,
    base_user_limit integer NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plans OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 28736)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(100) NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 28742)
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 28750)
-- Name: subscription_invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscription_invoices (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    subscription_id uuid NOT NULL,
    external_id character varying(255),
    payment_url character varying(255),
    amount numeric(12,2) NOT NULL,
    billing_month character varying(20) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    paid_at timestamp(0) without time zone,
    payment_details text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    payment_method public.payment_method_enum DEFAULT 'midtrans'::public.payment_method_enum NOT NULL,
    proof_of_payment character varying(500),
    admin_notes text,
    verified_at timestamp without time zone,
    verified_by uuid,
    payer_name character varying(255),
    payer_bank character varying(100),
    payer_account_number character varying(50),
    CONSTRAINT subscription_invoices_status_check CHECK (((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('paid'::character varying)::text, ('failed'::character varying)::text, ('expired'::character varying)::text])))
);


ALTER TABLE public.subscription_invoices OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 28765)
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscriptions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid NOT NULL,
    plan_id uuid,
    addons_user_count integer DEFAULT 0 NOT NULL,
    total_user_limit integer DEFAULT 0 NOT NULL,
    start_date timestamp(0) without time zone,
    end_date timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT subscriptions_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text, ('pending'::character varying)::text])))
);


ALTER TABLE public.subscriptions OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 37508)
-- Name: task_activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_activities (
    id uuid NOT NULL,
    workspace_id uuid NOT NULL,
    task_id uuid NOT NULL,
    user_id uuid NOT NULL,
    task_title character varying(255) NOT NULL,
    action_type character varying(255) NOT NULL,
    old_column character varying(255),
    new_column character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.task_activities OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 28778)
-- Name: task_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_assignments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    user_id uuid,
    assigned_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.task_assignments OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 28784)
-- Name: task_labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_labels (
    task_id uuid NOT NULL,
    label_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.task_labels OWNER TO postgres;

--
-- TOC entry 260 (class 1259 OID 28789)
-- Name: tasks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tasks (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    status character varying(100),
    board_column_id uuid,
    priority character varying(50),
    is_secret boolean DEFAULT false,
    start_datetime timestamp without time zone,
    due_datetime timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    phase character varying(100),
    completed_at timestamp with time zone
);


ALTER TABLE public.tasks OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 28800)
-- Name: user_companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    company_id uuid,
    roles_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    status_active boolean DEFAULT true NOT NULL
);


ALTER TABLE public.user_companies OWNER TO postgres;

--
-- TOC entry 262 (class 1259 OID 28807)
-- Name: user_workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    roles_id uuid,
    join_date timestamp without time zone DEFAULT now(),
    status_active boolean DEFAULT true,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_workspaces OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 28816)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    full_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password text NOT NULL,
    google_id character varying(255),
    status_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    avatar character varying(500),
    email_verified_at timestamp without time zone,
    onboarding_step character varying(255),
    has_seen_onboarding boolean DEFAULT false,
    onboarding_type character varying(255),
    system_role_id uuid
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 264 (class 1259 OID 28830)
-- Name: workspace_performance_snapshots; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspace_performance_snapshots (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    workspace_id uuid NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    period_type character varying(10) DEFAULT 'week'::character varying NOT NULL,
    metrics jsonb NOT NULL,
    performance_score integer DEFAULT 0 NOT NULL,
    quality_score integer DEFAULT 0 NOT NULL,
    risk_score integer DEFAULT 0 NOT NULL,
    suggestions jsonb NOT NULL,
    created_at timestamp(0) without time zone DEFAULT now(),
    updated_at timestamp(0) without time zone DEFAULT now(),
    version character varying(10) DEFAULT '1.0'::character varying NOT NULL
);


ALTER TABLE public.workspace_performance_snapshots OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 28854)
-- Name: workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid,
    type character varying(100),
    name character varying(255) NOT NULL,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    description text
);


ALTER TABLE public.workspaces OWNER TO postgres;

--
-- TOC entry 5099 (class 2604 OID 28864)
-- Name: feedbacks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks ALTER COLUMN id SET DEFAULT nextval('public.feedbacks_id_seq'::regclass);


--
-- TOC entry 5129 (class 2604 OID 28865)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 5143 (class 2604 OID 28866)
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- TOC entry 5559 (class 0 OID 28430)
-- Dependencies: 220
-- Data for Name: addons; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.addons (id, addon_name, price_per_user, description, is_active, created_at, updated_at) FROM stdin;
561693d6-c35a-480f-a52e-ae4947311905	Tambahan User	4000.00	Tambah 1 user	t	2026-06-21 20:35:42	2026-06-21 20:35:42
\.


--
-- TOC entry 5607 (class 0 OID 48588)
-- Dependencies: 268
-- Data for Name: ai_processing_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ai_processing_logs (id, user_id, workspace_id, project_name, payload, created_at, updated_at) FROM stdin;
81e4d921-feb6-4e7b-bba8-2a03aa6effaa	019f5015-b2cc-7144-82fa-fac0594f4e9b	5f1cb50a-2512-48f4-a184-e06a4665ef49	Kolabi	{"summary":{"project_name":"Kolabi","project_description":"Kolabi adalah platform manajemen proyek berbasis AI yang berfungsi sebagai pusat memori proyek (knowledge management base) untuk mencegah hilangnya konteks kerja pada tim remote dengan memproses transkrip rapat dan dokumen.","deliverables":["Aplikasi Project Management Memory (AI Brief Project)","Pitch Deck Kolabi yang Direvisi","Simulasi Struktur Biaya dan Strategi Harga","Dokumen Diferensiasi Produk (Positioning","Differentiation","Branding)"],"main_deadline":null},"tasks":[{"title":"Revisi Pitch Deck Kolabi","description":"Menyederhanakan slide problem\\/solusi, memindahkan slide risiko dan privasi data ke depan, memperbesar foto tim (zoom wajah), dan mengubah diagram teknologi menjadi bentuk loop.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Pitch Deck & Presentasi","action":"create","existing_task_id":null},{"title":"Analisis Diferensiasi Produk (PDB)","description":"Menyusun keunggulan kompetitif Kolabi dibanding kompetitor seperti Slack, fokus pada penyelesaian masalah spesifik lokal dan efisiensi kerja, bukan sekadar harga murah.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Strategi Bisnis","action":"create","existing_task_id":null},{"title":"Penyusunan Simulasi Biaya dan Strategi Harga","description":"Menghitung ulang biaya token AI per user, biaya server, operasional, serta merancang skema harga langganan (B2B\\/retail) yang sesuai dengan perilaku pengguna di Indonesia.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Strategi Bisnis","action":"create","existing_task_id":null},{"title":"Implementasi Progress Bar Pemrosesan AI","description":"Menambahkan visualisasi progress bar atau estimasi waktu tunggu saat AI sedang memproses dokumen\\/transkrip agar pengguna tidak menganggap aplikasi lemot.","priority":"medium","start_date":null,"deadline":null,"assignee_id":null,"phase":"Pengembangan Produk","action":"create","existing_task_id":null},{"title":"Klarifikasi Tanggal Pertemuan Offtaker","description":"Mengonfirmasi tanggal pasti pertemuan pilot testing dengan Offtaker yang dijadwalkan antara hari Selasa atau Rabu.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Klarifikasi","action":"create","existing_task_id":null},{"title":"Klarifikasi Metrik Efektivitas Produk","description":"Menentukan metrik atau KPI konkret (sebelum vs sesudah menggunakan Kolabi) untuk membuktikan peningkatan efisiensi kepada juri\\/investor.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Klarifikasi","action":"create","existing_task_id":null},{"title":"Klarifikasi Kebijakan Privasi Data AI","description":"Menyusun draf kebijakan privasi data (Privacy Policy) dan Master Service Agreement (MSA) untuk menjamin keamanan data proyek pengguna.","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Klarifikasi","action":"create","existing_task_id":null},{"title":"Klarifikasi Rencana Skalabilitas Sistem","description":"Menyusun rencana teknis dan simulasi kapasitas server untuk menangani lonjakan pengguna (misal hingga 1000 user sekaligus).","priority":"high","start_date":null,"deadline":null,"assignee_id":null,"phase":"Klarifikasi","action":"create","existing_task_id":null}],"decisions":[{"title":"Menggunakan Gemini API sebagai engine AI utama untuk pemrosesan brief","sources":["tanscript.docx"]},{"title":"Mengubah strategi harga dengan meniadakan opsi gratis selamanya (langsung berbayar atau trial 7 hari lalu wajib bayar)","sources":["tanscript.docx"]},{"title":"Menata ulang urutan slide pitch deck dengan menaruh risiko keamanan data dan privasi di bagian depan","sources":["tanscript.docx"]},{"title":"Mengubah visualisasi diagram alur teknologi dari bentuk tree menjadi circle\\/loop proses","sources":["tanscript.docx"]}],"missing_information":["Tanggal pasti pertemuan pilot testing dengan Offtaker (Selasa atau Rabu)","Metrik konkret\\/KPI efektivitas penggunaan aplikasi Kolabi (sebelum vs sesudah)","Draf awal Privacy Policy dan Master Service Agreement (MSA) terkait pemrosesan data oleh AI","Rencana teknis dan simulasi skalabilitas server untuk mengantisipasi lonjakan pengguna"],"clarification_questions":["Kapan tanggal pasti pertemuan dengan Offtaker untuk pelaksanaan pilot testing?","Bagaimana cara mengukur persentase efektivitas atau KPI peningkatan produktivitas setelah menggunakan Kolabi untuk dipresentasikan?","Apakah sudah ada draf awal untuk Privacy Policy dan Master Service Agreement (MSA) guna menjamin keamanan data pengguna?","Bagaimana rencana teknis dan kapasitas server untuk memastikan sistem tetap stabil saat terjadi lonjakan pengguna?"],"files_mapping":{"tanscript.docx":"cf964d18-8bc9-419c-9750-212ce85f24eb"}}	2026-09-12 21:08:08	2026-09-12 21:08:08
\.


--
-- TOC entry 5560 (class 0 OID 28441)
-- Dependencies: 221
-- Data for Name: announcement_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcement_recipients (id, announcement_id, user_id) FROM stdin;
\.


--
-- TOC entry 5561 (class 0 OID 28446)
-- Dependencies: 222
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, workspace_id, created_by, title, description, due_date, is_private, created_at, updated_at, auto_due, company_id) FROM stdin;
f7d68bac-fdd2-4b15-a4fe-71a59925c824	\N	019f5015-b2cc-7144-82fa-fac0594f4e9b	📢 Kick-off Campaign Marketing Q3 2026	<p>Halo seluruh tim,</p><p>Dalam rangka meningkatkan awareness produk dan memperkuat strategi pemasaran pada kuartal ketiga, perusahaan akan memulai <strong>Campaign Marketing Q3 2026</strong> mulai minggu ini.</p><p>Beberapa hal yang perlu diperhatikan:</p><ul><li>Seluruh divisi diminta mendukung pelaksanaan campaign sesuai dengan peran masing-masing.</li><li>Tim Marketing bertanggung jawab atas perencanaan, produksi, dan publikasi seluruh konten digital.</li><li>Tim Project Manager diminta memastikan setiap tugas memiliki timeline yang jelas dan progress diperbarui secara berkala di Koladi.</li><li>Seluruh anggota tim wajib melakukan update status tugas setiap hari kerja agar progres proyek dapat dipantau secara real-time.</li><li>Jika terdapat kendala atau potensi keterlambatan, segera laporkan kepada Project Manager melalui fitur komentar pada tugas terkait.</li></ul><p>Mari kita bekerja sama untuk memastikan campaign berjalan tepat waktu dan mencapai target yang telah ditetapkan.</p><p>Terima kasih atas kerja sama dan dedikasi seluruh tim.</p><p><strong>Management Koladi</strong></p>	2026-07-17	f	2026-07-16 18:06:25	2026-07-16 18:06:25	2026-07-17	05ff871f-a9e4-4603-bcbc-91ce388566d8
\.


--
-- TOC entry 5562 (class 0 OID 28457)
-- Dependencies: 223
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachments (id, attachable_type, attachable_id, file_url, uploaded_by, uploaded_at, file_name, file_size, file_type, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5563 (class 0 OID 28468)
-- Dependencies: 224
-- Data for Name: board_columns; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.board_columns (id, workspace_id, name, "position", created_by, created_at, updated_at, deleted_at) FROM stdin;
13cc46b5-96bc-469b-b245-9fa1ec7fc731	3505e3a5-936a-4177-94bf-e8b59bbb7e20	To Do List	1	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-21 21:07:32	2026-06-21 21:07:32	\N
7242fcfb-27ef-4dc7-a93f-7eaab996f031	3505e3a5-936a-4177-94bf-e8b59bbb7e20	Dikerjakan	2	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-21 21:07:32	2026-06-21 21:07:32	\N
7d37570a-2639-4a96-b9e6-86c6ec33255b	3505e3a5-936a-4177-94bf-e8b59bbb7e20	Selesai	3	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-21 21:07:32	2026-06-21 21:07:32	\N
ad4fc940-dee8-4c5e-83d5-23136c51e40f	3505e3a5-936a-4177-94bf-e8b59bbb7e20	Batal	4	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-21 21:07:32	2026-06-21 21:07:32	\N
f611e5a9-c27b-4ab2-8b0a-f2a4e75a9313	bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	To Do List	1	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	2026-06-28 21:32:00	2026-06-28 21:32:00	\N
493a2ee0-b8bc-4188-8916-6611dc2c7d6f	bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	Dikerjakan	2	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	2026-06-28 21:32:00	2026-06-28 21:32:00	\N
4ea5a187-7dd9-4e02-bd56-938d6827ac19	bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	Selesai	3	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	2026-06-28 21:32:00	2026-06-28 21:32:00	\N
7acb9afe-4cf1-433c-b8cc-852923b4d0e1	bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	Batal	4	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	2026-06-28 21:32:00	2026-06-28 21:32:00	\N
90c8588b-406d-477e-8c1f-35638eac14b2	a661a65f-3c55-4988-9a1a-e00b82045e7c	To Do List	1	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-30 15:11:26	2026-06-30 15:11:26	\N
2657a88e-8011-4da8-9654-baebb40ac567	a661a65f-3c55-4988-9a1a-e00b82045e7c	Dikerjakan	2	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-30 15:11:26	2026-06-30 15:11:26	\N
defc5b57-d53d-49f0-bf60-95ea632c32f9	a661a65f-3c55-4988-9a1a-e00b82045e7c	Selesai	3	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-30 15:11:26	2026-06-30 15:11:26	\N
eaa83d8b-b5e5-4b9f-aa69-c69259885b1d	a661a65f-3c55-4988-9a1a-e00b82045e7c	Batal	4	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-30 15:11:26	2026-06-30 15:11:26	\N
68ab1be9-d6c9-4cf9-9123-cf846372aaef	3e26510b-3f68-417e-bcb1-61cae095bec0	To Do List	1	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46	2026-07-10 23:26:46	\N
8d55ed3f-41d3-44fe-808e-a201a587fe65	3e26510b-3f68-417e-bcb1-61cae095bec0	Dikerjakan	2	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46	2026-07-10 23:26:46	\N
ac1490d9-d91d-481c-ab87-4433bf76c41b	3e26510b-3f68-417e-bcb1-61cae095bec0	Selesai	3	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46	2026-07-10 23:26:46	\N
4e1ee56b-b793-42c5-828d-98c5e62c59b7	3e26510b-3f68-417e-bcb1-61cae095bec0	Batal	4	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46	2026-07-10 23:26:46	\N
f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	04d422ef-974e-4f86-a430-d0bfec6f29a3	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:30:52	2026-07-11 14:30:52	\N
a33ed0dd-e70d-4470-be68-ffebf0ec97d9	04d422ef-974e-4f86-a430-d0bfec6f29a3	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:30:52	2026-07-11 14:30:52	\N
5162de8d-d199-4cd7-a6a2-2aeb7e59bf18	04d422ef-974e-4f86-a430-d0bfec6f29a3	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:30:52	2026-07-11 14:30:52	\N
15a4a18f-22b5-4514-8f4c-ac83ae105068	04d422ef-974e-4f86-a430-d0bfec6f29a3	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:30:52	2026-07-11 14:30:52	\N
6d6b37e6-8798-4d9f-aa92-39ed353288cb	00af975c-ad9b-4a10-bcf5-21fc788eef5c	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:34:27	2026-07-11 14:34:27	\N
dc8b5bba-5069-4d01-818f-6f40f88f56a1	00af975c-ad9b-4a10-bcf5-21fc788eef5c	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:34:27	2026-07-11 14:34:27	\N
1b62db84-6217-4521-9674-f183e273a2a8	00af975c-ad9b-4a10-bcf5-21fc788eef5c	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:34:27	2026-07-11 14:34:27	\N
8ce26bf1-fd99-4fda-9692-6fcabe85cae2	00af975c-ad9b-4a10-bcf5-21fc788eef5c	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:34:27	2026-07-11 14:34:27	\N
27bf7bf8-e489-4fcd-aede-8d9782c4c98c	5f1cb50a-2512-48f4-a184-e06a4665ef49	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:59:33	2026-07-16 17:59:33	\N
3ffd2485-a10e-4e39-a713-c00a84c6ade4	5f1cb50a-2512-48f4-a184-e06a4665ef49	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:59:33	2026-07-16 17:59:33	\N
14abcdb6-4e1e-4809-a6b8-7e107a8f2d01	5f1cb50a-2512-48f4-a184-e06a4665ef49	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:59:33	2026-07-16 17:59:33	\N
3b52b58d-424e-4d0f-b57b-70d76dfe2e27	5f1cb50a-2512-48f4-a184-e06a4665ef49	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:59:33	2026-07-16 17:59:33	\N
f878f3ef-484a-4c4f-ad02-4fe9d2482c3e	62cf7362-d1ac-4e13-9dee-7694082ef7b9	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:40	2026-07-16 18:09:40	\N
172b6236-299b-4ef4-b8b2-5078915df1f4	62cf7362-d1ac-4e13-9dee-7694082ef7b9	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:40	2026-07-16 18:09:40	\N
9f9bba3a-1161-4b75-b008-bc534d91d3aa	62cf7362-d1ac-4e13-9dee-7694082ef7b9	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:40	2026-07-16 18:09:40	\N
54d1413e-32d0-4943-9e69-f0fce82a5950	62cf7362-d1ac-4e13-9dee-7694082ef7b9	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:40	2026-07-16 18:09:40	\N
6dae3be1-31f6-45c2-a507-360a96127c1a	1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:10:04	2026-07-16 18:10:04	\N
34511285-6d13-4744-8274-fdd83d79ee84	1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:10:04	2026-07-16 18:10:04	\N
3d51debc-31ba-453f-b49c-75d5b194781b	1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:10:04	2026-07-16 18:10:04	\N
e6df4dff-83e0-49e6-bf8f-160079dc89d3	1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:10:04	2026-07-16 18:10:04	\N
c98588bd-b2b3-4221-930c-1dadb4a8cb64	878834d3-87ad-459f-91e7-95536d600c4c	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 11:45:18	2026-08-19 11:45:18	\N
98ab3f73-8033-4576-b4bf-a662d64acad2	878834d3-87ad-459f-91e7-95536d600c4c	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 11:45:18	2026-08-19 11:45:18	\N
3d965821-f48b-49b9-8d64-bbf7103d1fce	878834d3-87ad-459f-91e7-95536d600c4c	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 11:45:18	2026-08-19 11:45:18	\N
b7b5c338-f1bf-41c7-b6d9-221b3c1ad758	878834d3-87ad-459f-91e7-95536d600c4c	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 11:45:18	2026-08-19 11:45:18	\N
06064bad-30ca-472c-8aa4-0792107e7b89	260cc0d8-81b0-453d-91b8-67d9ca92878a	To Do List	1	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 19:51:40	2026-08-26 19:51:40	\N
baf3ee5a-65fe-462f-94a1-4b9857f564f6	260cc0d8-81b0-453d-91b8-67d9ca92878a	Dikerjakan	2	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 19:51:40	2026-08-26 19:51:40	\N
14ea3e8c-991a-4a80-b205-f8b4d2ae8af3	260cc0d8-81b0-453d-91b8-67d9ca92878a	Selesai	3	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 19:51:40	2026-08-26 19:51:40	\N
5f8001c4-2c39-45f8-90af-b9067177ec4b	260cc0d8-81b0-453d-91b8-67d9ca92878a	Batal	4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 19:51:40	2026-08-26 19:51:40	\N
\.


--
-- TOC entry 5564 (class 0 OID 28476)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-board_columns_bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	TzozOToiSWxsdW1pbmF0ZVxEYXRhYmFzZVxFbG9xdWVudFxDb2xsZWN0aW9uIjoyOntzOjg6IgAqAGl0ZW1zIjthOjQ6e2k6MDtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImY2MTFlNWE5LWMyN2ItNGFiMi04YjBhLWYyYTRlNzVhOTMxMyI7czo0OiJuYW1lIjtzOjEwOiJUbyBEbyBMaXN0IjtzOjg6InBvc2l0aW9uIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJiZDNjODdlZC02ZmM5LTRmN2QtOTA1Ny1kNTNjY2FmM2MwNmUiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJmNjExZTVhOS1jMjdiLTRhYjItOGIwYS1mMmE0ZTc1YTkzMTMiO3M6NDoibmFtZSI7czoxMDoiVG8gRG8gTGlzdCI7czo4OiJwb3NpdGlvbiI7aToxO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYmQzYzg3ZWQtNmZjOS00ZjdkLTkwNTctZDUzY2NhZjNjMDZlIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MTtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjQ5M2EyZWUwLWI4YmMtNDE4OC04OTE2LTY2MTFkYzJjN2Q2ZiI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJiZDNjODdlZC02ZmM5LTRmN2QtOTA1Ny1kNTNjY2FmM2MwNmUiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI0OTNhMmVlMC1iOGJjLTQxODgtODkxNi02NjExZGMyYzdkNmYiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYmQzYzg3ZWQtNmZjOS00ZjdkLTkwNTctZDUzY2NhZjNjMDZlIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjRlYTVhMTg3LTdkZDktNGUwMi1iZDU2LTkzOGQ2ODI3YWMxOSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImJkM2M4N2VkLTZmYzktNGY3ZC05MDU3LWQ1M2NjYWYzYzA2ZSI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjRlYTVhMTg3LTdkZDktNGUwMi1iZDU2LTkzOGQ2ODI3YWMxOSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImJkM2M4N2VkLTZmYzktNGY3ZC05MDU3LWQ1M2NjYWYzYzA2ZSI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1782658363
laravel-cache-board_columns_a661a65f-3c55-4988-9a1a-e00b82045e7c	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjI2NTdhODhlLTgwMTEtNGRhOC05NjU0LWJhZWJiNDBhYzU2NyI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJhNjYxYTY1Zi0zYzU1LTQ5ODgtOWExYS1lMDBiODIwNDVlN2MiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiIyNjU3YTg4ZS04MDExLTRkYTgtOTY1NC1iYWViYjQwYWM1NjciO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYTY2MWE2NWYtM2M1NS00OTg4LTlhMWEtZTAwYjgyMDQ1ZTdjIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImRlZmM1YjU3LWQ1M2QtNDlmMC1iZjYwLTk1ZWE2MzJjMzJmOSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE2NjFhNjVmLTNjNTUtNDk4OC05YTFhLWUwMGI4MjA0NWU3YyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImRlZmM1YjU3LWQ1M2QtNDlmMC1iZjYwLTk1ZWE2MzJjMzJmOSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE2NjFhNjVmLTNjNTUtNDk4OC05YTFhLWUwMGI4MjA0NWU3YyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1782808229
laravel-cache-board_columns_3505e3a5-936a-4177-94bf-e8b59bbb7e20	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjcyNDJmY2ZiLTI3ZWYtNGRjNy1hOTNmLTdlYWFiOTk2ZjAzMSI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIzNTA1ZTNhNS05MzZhLTQxNzctOTRiZi1lOGI1OWJiYjdlMjAiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI3MjQyZmNmYi0yN2VmLTRkYzctYTkzZi03ZWFhYjk5NmYwMzEiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiMzUwNWUzYTUtOTM2YS00MTc3LTk0YmYtZThiNTliYmI3ZTIwIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjdkMzc1NzBhLTI2MzktNGE5Ni1iOWU2LTg2YzZlYzMzMjU1YiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjM1MDVlM2E1LTkzNmEtNDE3Ny05NGJmLWU4YjU5YmJiN2UyMCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjdkMzc1NzBhLTI2MzktNGE5Ni1iOWU2LTg2YzZlYzMzMjU1YiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjM1MDVlM2E1LTkzNmEtNDE3Ny05NGJmLWU4YjU5YmJiN2UyMCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1782808339
laravel-cache-board_columns_3e26510b-3f68-417e-bcb1-61cae095bec0	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjhkNTVlZDNmLTQxZDMtNDRmZS04MDhlLWEyMDFhNTg3ZmU2NSI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIzZTI2NTEwYi0zZjY4LTQxN2UtYmNiMS02MWNhZTA5NWJlYzAiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI4ZDU1ZWQzZi00MWQzLTQ0ZmUtODA4ZS1hMjAxYTU4N2ZlNjUiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiM2UyNjUxMGItM2Y2OC00MTdlLWJjYjEtNjFjYWUwOTViZWMwIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImFjMTQ5MGQ5LWQ5MWQtNDgxYy1hYjg3LTQ0MzNiZjc2YzQxYiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjNlMjY1MTBiLTNmNjgtNDE3ZS1iY2IxLTYxY2FlMDk1YmVjMCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImFjMTQ5MGQ5LWQ5MWQtNDgxYy1hYjg3LTQ0MzNiZjc2YzQxYiI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjNlMjY1MTBiLTNmNjgtNDE3ZS1iY2IxLTYxY2FlMDk1YmVjMCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1783701120
laravel-cache-board_columns_04d422ef-974e-4f86-a430-d0bfec6f29a3	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImEzM2VkMGRkLWU3MGQtNDQ3MC1iZTY4LWZmZWJmMGVjOTdkOSI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIwNGQ0MjJlZi05NzRlLTRmODYtYTQzMC1kMGJmZWM2ZjI5YTMiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJhMzNlZDBkZC1lNzBkLTQ0NzAtYmU2OC1mZmViZjBlYzk3ZDkiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiMDRkNDIyZWYtOTc0ZS00Zjg2LWE0MzAtZDBiZmVjNmYyOWEzIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjUxNjJkZThkLWQxOTktNGNkNy1hNmEyLTJhZWI3ZTU5YmYxOCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjA0ZDQyMmVmLTk3NGUtNGY4Ni1hNDMwLWQwYmZlYzZmMjlhMyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjUxNjJkZThkLWQxOTktNGNkNy1hNmEyLTJhZWI3ZTU5YmYxOCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjA0ZDQyMmVmLTk3NGUtNGY4Ni1hNDMwLWQwYmZlYzZmMjlhMyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO31pOjM7TzoyMjoiQXBwXE1vZGVsc1xCb2FyZENvbHVtbiI6MzU6e3M6MTM6IgAqAGNvbm5lY3Rpb24iO3M6NToicGdzcWwiO3M6ODoiACoAdGFibGUiO3M6MTM6ImJvYXJkX2NvbHVtbnMiO3M6MTM6IgAqAHByaW1hcnlLZXkiO3M6MjoiaWQiO3M6MTA6IgAqAGtleVR5cGUiO3M6Njoic3RyaW5nIjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MDtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo0OntzOjI6ImlkIjtzOjM2OiIxNWE0YTE4Zi0yMmI1LTQ1MTQtOGY0Yy1hYzgzYWUxMDUwNjgiO3M6NDoibmFtZSI7czo1OiJCYXRhbCI7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiMDRkNDIyZWYtOTc0ZS00Zjg2LWE0MzAtZDBiZmVjNmYyOWEzIjt9czoxMToiACoAb3JpZ2luYWwiO2E6NDp7czoyOiJpZCI7czozNjoiMTVhNGExOGYtMjJiNS00NTE0LThmNGMtYWM4M2FlMTA1MDY4IjtzOjQ6Im5hbWUiO3M6NToiQmF0YWwiO3M6ODoicG9zaXRpb24iO2k6NDtzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjA0ZDQyMmVmLTk3NGUtNGY4Ni1hNDMwLWQwYmZlYzZmMjlhMyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO319czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1784188511
laravel-cache-board_columns_878834d3-87ad-459f-91e7-95536d600c4c	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6Ijk4YWIzZjczLTgwMzMtNDU3Ni1iNGJmLWE2NjJkNjRhY2FkMiI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiI4Nzg4MzRkMy04N2FkLTQ1OWYtOTFlNy05NTUzNmQ2MDBjNGMiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI5OGFiM2Y3My04MDMzLTQ1NzYtYjRiZi1hNjYyZDY0YWNhZDIiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiODc4ODM0ZDMtODdhZC00NTlmLTkxZTctOTU1MzZkNjAwYzRjIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjNkOTY1ODIxLWY0OGItNDliOS04ZDY0LWJiZjcxMDNkMWZjZSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6Ijg3ODgzNGQzLTg3YWQtNDU5Zi05MWU3LTk1NTM2ZDYwMGM0YyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjNkOTY1ODIxLWY0OGItNDliOS04ZDY0LWJiZjcxMDNkMWZjZSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6Ijg3ODgzNGQzLTg3YWQtNDU5Zi05MWU3LTk1NTM2ZDYwMGM0YyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1787488793
laravel-cache-board_columns_260cc0d8-81b0-453d-91b8-67d9ca92878a	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImJhZjNlZTVhLTY1ZmUtNDYyZi05NGExLTRiOTg1N2Y1NjRmNiI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIyNjBjYzBkOC04MWIwLTQ1M2QtOTFiOC02N2Q5Y2E5Mjg3OGEiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJiYWYzZWU1YS02NWZlLTQ2MmYtOTRhMS00Yjk4NTdmNTY0ZjYiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiMjYwY2MwZDgtODFiMC00NTNkLTkxYjgtNjdkOWNhOTI4NzhhIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjE0ZWEzZThjLTk5MWEtNGE4MC1iMjA1LWY4YjRkMmFlOGFmMyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjI2MGNjMGQ4LTgxYjAtNDUzZC05MWI4LTY3ZDljYTkyODc4YSI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjE0ZWEzZThjLTk5MWEtNGE4MC1iMjA1LWY4YjRkMmFlOGFmMyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjI2MGNjMGQ4LTgxYjAtNDUzZC05MWI4LTY3ZDljYTkyODc4YSI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1787758384
laravel-cache-board_columns_5f1cb50a-2512-48f4-a184-e06a4665ef49	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjNmZmQyNDg1LWExMGUtNGUzOS1hNzEzLWMwMGE4NGM2YWRlNCI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiI1ZjFjYjUwYS0yNTEyLTQ4ZjQtYTE4NC1lMDZhNDY2NWVmNDkiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiIzZmZkMjQ4NS1hMTBlLTRlMzktYTcxMy1jMDBhODRjNmFkZTQiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiNWYxY2I1MGEtMjUxMi00OGY0LWExODQtZTA2YTQ2NjVlZjQ5Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjE0YWJjZGI2LTRlMWUtNDgwOS1hNmI4LTdlMTA3YThmMmQwMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjVmMWNiNTBhLTI1MTItNDhmNC1hMTg0LWUwNmE0NjY1ZWY0OSI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjE0YWJjZGI2LTRlMWUtNDgwOS1hNmI4LTdlMTA3YThmMmQwMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjVmMWNiNTBhLTI1MTItNDhmNC1hMTg0LWUwNmE0NjY1ZWY0OSI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1789222392
\.


--
-- TOC entry 5565 (class 0 OID 28484)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5566 (class 0 OID 28492)
-- Dependencies: 227
-- Data for Name: calendar_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_events (id, workspace_id, created_by, title, description, start_datetime, end_datetime, recurrence, is_private, is_online_meeting, meeting_link, created_at, updated_at, deleted_at, company_id, location) FROM stdin;
019f6a9c-9284-73af-8c3a-abd77768c7b9	\N	019f5015-b2cc-7144-82fa-fac0594f4e9b	Rapat Bersama	\N	2026-07-16 18:07:00	2026-07-16 19:07:00	\N	f	f	\N	2026-07-16 18:07:53	2026-07-16 18:07:53	\N	05ff871f-a9e4-4603-bcbc-91ce388566d8	Ruang meeting lt.3
019f6a9d-22f9-721b-9296-47b0aae7dc16	\N	019f5015-b2cc-7144-82fa-fac0594f4e9b	Rapat dept it	\N	2026-07-17 18:08:00	2026-07-17 19:08:00	\N	f	t	http://localhost:8000/jadwal-umum/buat	2026-07-16 18:08:30	2026-07-16 18:08:30	\N	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N
\.


--
-- TOC entry 5567 (class 0 OID 28504)
-- Dependencies: 228
-- Data for Name: calendar_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_participants (id, event_id, user_id, status, attendance) FROM stdin;
20bb2cdc-5eb2-4ab3-a329-41a463952569	019f6a9c-9284-73af-8c3a-abd77768c7b9	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	accepted	f
82ba0a6a-7385-4b3c-bc35-c2e8e772f1e1	019f6a9c-9284-73af-8c3a-abd77768c7b9	019f5015-b2cc-7144-82fa-fac0594f4e9b	accepted	f
651ac8e1-ea9d-48c8-afd9-2bced2f882ef	019f6a9c-9284-73af-8c3a-abd77768c7b9	019f6a8f-c28f-7090-97d5-113e5d166b06	accepted	f
cb2b9fc3-29c7-43b3-8de5-cf0e45798b56	019f6a9d-22f9-721b-9296-47b0aae7dc16	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	accepted	f
9f0aeed6-4d6f-40c4-947c-0cc65b0262c4	019f6a9d-22f9-721b-9296-47b0aae7dc16	019f5015-b2cc-7144-82fa-fac0594f4e9b	accepted	f
29921d7c-5266-463a-a1d6-0c58dc5efc8b	019f6a9d-22f9-721b-9296-47b0aae7dc16	019f6a8f-c28f-7090-97d5-113e5d166b06	accepted	f
\.


--
-- TOC entry 5568 (class 0 OID 28510)
-- Dependencies: 229
-- Data for Name: checklists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.checklists (id, task_id, title, is_done, created_at, updated_at, "position") FROM stdin;
\.


--
-- TOC entry 5569 (class 0 OID 28521)
-- Dependencies: 230
-- Data for Name: colors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.colors (id, rgb) FROM stdin;
2a854d03-557a-4457-b395-136d0baafcf5	#795548
2cf4a4f4-06a0-47c7-876b-2cf788e16351	#FF9800
34baa825-01f9-4b26-8fc4-519dfca5af6b	#9E9E9E
3f9fedb9-d632-4a8f-860a-05e0781fb70c	#000000
4712cec0-289c-44bd-9592-7bdaa5dbe883	#00796B
4ce6f29c-fbf7-4ade-b6a3-4e49ac28dec3	#8BC34A
5c0a1142-25ef-4373-9164-6137718a5b5f	#FFFFFF
62f25f1b-506e-4f9e-a0f5-bac0bc6d543d	#FF5722
69bed3f4-1659-49c1-8bbe-820e135f781f	#FF4C4C
6c8f270d-fc3d-45fa-a35a-64be13a797e9	#303F9F
6f736fa7-7b45-4b05-a3d4-cdad4851c02b	#607D8B
75442d05-1b79-446f-ae39-d3698531caa9	#FFC107
780ac390-64ae-4e4c-b599-cd07c0bfe105	#0288D1
792985e0-1d2f-4830-b296-4c06a97a0c64	#7B1FA2
7f07267f-c7cb-430a-b840-4bf68072454b	#3F51B5
7f0e2934-5c76-41b6-b1bd-eb57c5509d53	#00BCD4
828fd126-71c8-4692-b990-a067faa78b2b	#512DA8
97176aa4-a936-42f4-9a67-b07fa555ad87	#C2185B
986e4c53-cf2a-4b8a-89ed-aa4fca0a00fb	#2E7D32
98d8ecc3-3b2c-4e2a-9f54-7138c43534e1	#CDDC39
9ca431e2-97f8-4eba-9656-0286baf96ee9	#4CAF50
a222164c-e36e-43ad-b9bb-6867f94c3f43	#009688
b2ed830a-1187-477c-813f-dbba8cf3114a	#FFEB3B
b7265dd1-078e-4365-a718-4a9736c43698	#E91E63
cee15dee-c5b0-477b-a9ea-d511e64f3b31	#FFCDD2
cfe9ecde-3169-4db8-b535-67ac6d728ff9	#03A9F4
d6d3bbb2-c943-433c-ac71-c451925134f7	#2196F3
f4f8cdec-f51b-439d-a4ca-38a232480fa3	#F44336
fa32a7da-4d6b-4149-ac51-588bb48f6555	#673AB7
fe4955e8-9aad-4677-b0d8-3665c5c84b53	#9C27B0
\.


--
-- TOC entry 5570 (class 0 OID 28527)
-- Dependencies: 231
-- Data for Name: comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comments (id, parent_comment_id, commentable_type, commentable_id, user_id, content, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5571 (class 0 OID 28537)
-- Dependencies: 232
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, email, address, phone, created_at, updated_at, deleted_at, trial_start, trial_end, status) FROM stdin;
fc21fabc-e558-4d71-b68e-616e6bdadb73	Polibatam	\N	\N	\N	2026-06-21 20:42:49	2026-06-28 21:28:11	\N	2026-06-21 20:42:49	2026-06-28 20:42:49	expired
5c8f25d9-540f-4673-a788-424129bde7df	Koladi	\N	\N	\N	2026-06-28 21:31:29	2026-06-28 21:31:29	\N	2026-06-28 21:31:27	2026-07-05 21:31:27	trial
f263faec-5824-4c51-b4b3-6bdd001ab84f	Batu Aji	\N	\N	\N	2026-06-30 15:10:01	2026-06-30 15:10:01	\N	2026-06-30 15:10:00	2026-07-07 15:10:00	trial
c6eba6e5-5e74-4523-9063-40cae1888779	Batu Aji	\N	\N	\N	2026-06-30 15:10:02	2026-06-30 15:10:02	\N	2026-06-30 15:10:02	2026-07-07 15:10:02	trial
c785040c-a4cc-4be8-a752-8a5ee215dc72	AI	\N	\N	\N	2026-07-10 22:32:55	2026-07-10 22:32:55	\N	2026-07-10 22:32:54	2026-07-17 22:32:54	trial
05ff871f-a9e4-4603-bcbc-91ce388566d8	AI	\N	\N	\N	2026-07-11 14:30:33	2026-08-27 11:07:56	\N	2026-07-11 14:30:33	2026-07-18 14:30:33	active
2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1	kol	\N	\N	\N	2026-08-26 19:49:49	2026-08-27 11:08:26	2026-08-27 11:08:26	2026-08-26 19:49:49	2026-09-02 19:49:49	trial
6987db02-5cb8-470e-80f1-1e1816ab829a	Test Company	\N	\N	\N	2026-07-15 18:01:25	2026-08-27 11:08:35	2026-08-27 11:08:35	2026-07-15 18:01:25	2026-07-22 18:01:25	trial
8253bba3-e703-4301-abee-a0b744e4fa81	ai	\N	\N	\N	2026-08-19 11:44:23	2026-08-27 11:08:44	2026-08-27 11:08:44	2026-08-19 11:44:22	2026-08-26 11:44:22	trial
\.


--
-- TOC entry 5572 (class 0 OID 28550)
-- Dependencies: 233
-- Data for Name: conversation_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversation_participants (id, conversation_id, user_id, joined_at, is_admin, last_read_at) FROM stdin;
e6247e83-cef4-4573-8350-30a064da951a	4a075eb0-fc03-4e1a-9670-a7ed918931dc	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:31:24.310076	f	\N
1e808fdd-6955-423f-a0c6-e009a43c9dff	c6f9c0a1-13ee-4e52-a9b6-db86f176cdcc	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:44:59.96615	f	\N
d3bc99c1-64b3-40f5-b0e8-868ef14a908b	61813e3c-746a-4195-966a-2ddd966213b4	019f6a8f-c28f-7090-97d5-113e5d166b06	2026-07-16 18:06:53.113902	f	\N
3c5c654c-59ff-463a-b8d6-7e4de7c92add	61813e3c-746a-4195-966a-2ddd966213b4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:06:53.113902	t	2026-07-16 18:06:55
a1bb5bcd-3f18-4850-a0e6-d2b9710b5fae	30766506-f241-4acb-95bc-673c3885ad0b	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-16 18:06:56.85105	f	\N
d9601145-6b58-48ba-bb0d-e52dd1ad2941	30766506-f241-4acb-95bc-673c3885ad0b	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:06:56.85105	t	2026-07-16 18:06:58
2ca22293-0a31-48b3-bff0-3797311ba6b5	6329ebb1-aa14-4f7f-bfeb-b9b2118bb255	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:46:44.3823	f	\N
999369a7-dc2d-4862-a64e-3234f711f7f3	01866bf9-b1c2-4635-b17c-bce295955ae4	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 20:10:51.852905	f	\N
\.


--
-- TOC entry 5573 (class 0 OID 28557)
-- Dependencies: 234
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, workspace_id, created_at, type, name, created_by, updated_at, last_message_id, scope, company_id) FROM stdin;
4a075eb0-fc03-4e1a-9670-a7ed918931dc	04d422ef-974e-4f86-a430-d0bfec6f29a3	2026-07-11 14:31:24	group	koladi	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:31:24	\N	workspace	\N
c6f9c0a1-13ee-4e52-a9b6-db86f176cdcc	\N	2026-07-16 17:44:59	group	AI	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:44:59	\N	company	05ff871f-a9e4-4603-bcbc-91ce388566d8
61813e3c-746a-4195-966a-2ddd966213b4	\N	2026-07-16 18:06:53	private	\N	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:06:53	\N	company	05ff871f-a9e4-4603-bcbc-91ce388566d8
30766506-f241-4acb-95bc-673c3885ad0b	\N	2026-07-16 18:06:56	private	\N	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:06:56	\N	company	05ff871f-a9e4-4603-bcbc-91ce388566d8
6329ebb1-aa14-4f7f-bfeb-b9b2118bb255	260cc0d8-81b0-453d-91b8-67d9ca92878a	2026-08-26 21:46:44	group	ai	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:46:44	\N	workspace	\N
01866bf9-b1c2-4635-b17c-bce295955ae4	5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 20:10:51	group	Marketing	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 20:10:51	\N	workspace	\N
\.


--
-- TOC entry 5606 (class 0 OID 37523)
-- Dependencies: 267
-- Data for Name: decisions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.decisions (id, workspace_id, created_by, title, description, decision_date, evidence_file_id, is_validated, validated_by, validated_at, created_at, updated_at) FROM stdin;
500d0f78-5760-484f-87f7-c5ad8a57bd6a	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Penggunaan Bahasa Indonesia dalam presentasi dan proposal diperbolehkan untuk menjaga keyakinan penyampaian peserta.	\N	2026-07-15	a4224e27-4cb0-449a-970a-2e11a4611424	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58
b96d58ce-0c84-45c5-ad50-3a7fbbb2ff16	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Durasi maksimal video submission ditetapkan selama 3 menit dengan pembagian konten pitching investor dan demo produk.	\N	2026-07-15	a4224e27-4cb0-449a-970a-2e11a4611424	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58
6ecbf07e-9115-47ac-82cb-29180bf1d333	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Tidak disediakan infrastruktur Google Colab berbayar pada tahap ini; peserta diarahkan untuk melakukan bootstrapping atau memanfaatkan promo gratis.	\N	2026-07-15	a4224e27-4cb0-449a-970a-2e11a4611424	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58
2e65e007-4301-4ac1-a53b-581fa5fa922a	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Penggunaan Pendekatan Non-Preemptive untuk Menghindari Interupsi Alur Kerja Pengguna	\N	2026-07-15	80b82c15-01bc-46c3-9527-efb536741bff	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25
8c3fa9d8-f3d9-4deb-835a-a9f8e826897c	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Klasifikasi Urgensi Tugas Menjadi Empat Tingkat (Overdue, Critical, Warning, Normal)	\N	2026-07-15	80b82c15-01bc-46c3-9527-efb536741bff	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25
bcb1df4e-3a2e-4606-a9be-d8f44d9060b0	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Implementasi Logika EDF pada Backend Laravel (TaskController.php)	\N	2026-07-15	80b82c15-01bc-46c3-9527-efb536741bff	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25
af4ea15b-02a2-4eeb-a8eb-2e5a659800e1	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Visualisasi Urgensi Menggunakan Badge Warna dan Aksen Border Kiri pada Kartu Kanban	\N	2026-07-15	80b82c15-01bc-46c3-9527-efb536741bff	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25
6d9641ae-e731-43bb-809f-ea2c07cff9db	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menggunakan Gemini API sebagai engine AI utama untuk pemrosesan brief	\N	2026-09-12	cf964d18-8bc9-419c-9750-212ce85f24eb	f	\N	\N	2026-09-12 21:08:05	2026-09-12 21:08:05
c5242d6a-4ca8-4fb4-b650-4e313ae17f38	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Mengubah strategi harga dengan meniadakan opsi gratis selamanya (langsung berbayar atau trial 7 hari lalu wajib bayar)	\N	2026-09-12	cf964d18-8bc9-419c-9750-212ce85f24eb	f	\N	\N	2026-09-12 21:08:07	2026-09-12 21:08:07
ca233be1-e83a-4675-90bc-0a6b23bc75ef	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menata ulang urutan slide pitch deck dengan menaruh risiko keamanan data dan privasi di bagian depan	\N	2026-09-12	cf964d18-8bc9-419c-9750-212ce85f24eb	f	\N	\N	2026-09-12 21:08:07	2026-09-12 21:08:07
8e8e7586-c7ce-4f4a-a577-c0b55776fc54	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Mengubah visualisasi diagram alur teknologi dari bentuk tree menjadi circle/loop proses	\N	2026-09-12	cf964d18-8bc9-419c-9750-212ce85f24eb	f	\N	\N	2026-09-12 21:08:07	2026-09-12 21:08:07
\.


--
-- TOC entry 5574 (class 0 OID 28566)
-- Dependencies: 235
-- Data for Name: document_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.document_recipients (id, document_id, user_id, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5575 (class 0 OID 28575)
-- Dependencies: 236
-- Data for Name: feedbacks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.feedbacks (id, name, email, message, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5577 (class 0 OID 28585)
-- Dependencies: 238
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.files (id, folder_id, workspace_id, file_url, is_private, uploaded_by, uploaded_at, file_name, file_path, file_size, file_type, company_id) FROM stdin;
2918b4c2-97c5-4f50-b502-3a31f953faa9	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 22:47:01	Pojok Curhat Kak Pidi II.txt	files/Pojok Curhat Kak Pidi II.txt	65512	txt	\N
97a46d1c-7820-4a25-8b20-f24c9bc734a1	80501ac6-ae7b-4ddf-a77b-bcfa7cdbb9cb	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/manuscript-r0.pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 22:51:43	manuscript-r0.pdf	files/manuscript-r0.pdf	3025213	pdf	\N
a4224e27-4cb0-449a-970a-2e11a4611424	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II(1).txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:01:41	Pojok Curhat Kak Pidi II(1).txt	files/Pojok Curhat Kak Pidi II(1).txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
094b77f2-64f2-45fd-a161-1952b3a5b494	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Format Laporan New - 2026.pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:06:56	Format Laporan New - 2026.pdf	files/Format Laporan New - 2026.pdf	1298350	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
e67abf6f-8f43-454c-a3d2-632ffada8d58	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Format Laporan New - 2026(1).pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:07:23	Format Laporan New - 2026(1).pdf	files/Format Laporan New - 2026(1).pdf	1298350	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
12d5f98f-2bb4-456f-9b08-ba220f3a9209	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/1763440531_D9We3s6y.docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:07:59	1763440531_D9We3s6y.docx	files/1763440531_D9We3s6y.docx	26495	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
08cd70b4-0a71-48af-9f4a-deb72486db45	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Manuscript_Anonymous.docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:08:37	Manuscript_Anonymous.docx	files/Manuscript_Anonymous.docx	796607	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
0fd902a8-597a-4130-9cfd-d8ce6693e226	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/2213-1-5946-1-10-20251231.pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:14:19	2213-1-5946-1-10-20251231.pdf	files/2213-1-5946-1-10-20251231.pdf	566091	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
80b82c15-01bc-46c3-9527-efb536741bff	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Manuscript_with_Author_Details (1).docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:14:54	Manuscript_with_Author_Details (1).docx	files/Manuscript_with_Author_Details (1).docx	850296	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
6d5ff7c5-6591-46d1-bdee-656c7c826d89	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Manuscript_Anonymous(1).docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:19:27	Manuscript_Anonymous(1).docx	files/Manuscript_Anonymous(1).docx	796607	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
27ff4cbb-aa47-49b2-9032-5e3ee9b834da	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/link 2.pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:33:56	link 2.pdf	files/link 2.pdf	1003114	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
ee857f24-39e4-42b2-b4ad-8c30503f898f	\N	04d422ef-974e-4f86-a430-d0bfec6f29a3	http://localhost:8000/storage/files/Dokumentasi_Perubahan_Paper_Koladi.docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-15 17:34:20	Dokumentasi_Perubahan_Paper_Koladi.docx	files/Dokumentasi_Perubahan_Paper_Koladi.docx	30080	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
776cd947-4577-44b6-aac4-f6811b5dff99	\N	\N	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:01	Pojok Curhat Kak Pidi II.txt	files/Pojok Curhat Kak Pidi II.txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
0784ddce-bd5a-4157-9779-68febe61f761	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-17 13:20:22	Pojok Curhat Kak Pidi II.txt	files/Pojok Curhat Kak Pidi II.txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
768abb69-f543-4658-ac53-52a75317ad73	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II(1).txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-17 13:22:36	Pojok Curhat Kak Pidi II(1).txt	files/Pojok Curhat Kak Pidi II(1).txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
2d96d130-fe07-4536-9086-0358c8d2fb2f	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II(2).txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-17 13:23:18	Pojok Curhat Kak Pidi II(2).txt	files/Pojok Curhat Kak Pidi II(2).txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
4c7e8342-3ccc-4a5d-b44e-092c9b376e3b	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pojok Curhat Kak Pidi II(3).txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-17 13:24:21	Pojok Curhat Kak Pidi II(3).txt	files/Pojok Curhat Kak Pidi II(3).txt	65512	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
94ec9895-0c5a-4e44-86b9-49af6f326115	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_proyek_pembangunan_konstruksi_1787753660.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:14:20	Template Konteks - proyek pembangunan konstruksi.txt	files/template_konteks_proyek_pembangunan_konstruksi_1787753660.txt	1967	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
499baa89-57bd-4682-9884-32cb3c638fd3	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi1_1787753731.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:15:31	Template Konteks - pengembangan website aplikasi(1).txt	files/template_konteks_pengembangan_website_aplikasi1_1787753731.txt	1892	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
b7a93ca2-3c56-4bec-b249-e8b43e2ca93b	\N	878834d3-87ad-459f-91e7-95536d600c4c	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi_1787150631.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 21:43:52	Template Konteks - pengembangan website aplikasi.txt	files/template_konteks_pengembangan_website_aplikasi_1787150631.txt	862	txt	8253bba3-e703-4301-abee-a0b744e4fa81
f1e47ecf-462e-44c3-9b76-b57a2dfec88a	\N	878834d3-87ad-459f-91e7-95536d600c4c	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi1_1787487722.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-23 19:22:03	Template Konteks - pengembangan website aplikasi(1).txt	files/template_konteks_pengembangan_website_aplikasi1_1787487722.txt	862	txt	8253bba3-e703-4301-abee-a0b744e4fa81
7ad1a9f5-2dba-4d17-ac28-d2abbdf98d8a	\N	878834d3-87ad-459f-91e7-95536d600c4c	http://localhost:8000/storage/files/template_konteks_kampanye_digital_marketing_branding_1787489766.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-23 19:56:06	Template Konteks - kampanye digital marketing branding.txt	files/template_konteks_kampanye_digital_marketing_branding_1787489766.txt	844	txt	8253bba3-e703-4301-abee-a0b744e4fa81
36319206-83f3-4361-bb27-70387f907bc0	\N	878834d3-87ad-459f-91e7-95536d600c4c	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi2_1787490748.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-23 20:12:28	Template Konteks - pengembangan website aplikasi(2).txt	files/template_konteks_pengembangan_website_aplikasi2_1787490748.txt	1430	txt	8253bba3-e703-4301-abee-a0b744e4fa81
80aa1e6d-fea0-4164-b6d5-e3220d0b52f8	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi_1787749826.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 20:10:27	Template Konteks - pengembangan website aplikasi.txt	files/template_konteks_pengembangan_website_aplikasi_1787749826.txt	1430	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
95629150-5f8a-4f9f-b700-ac6d182e55ae	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_kampanye_digital_marketing_branding_1787752654.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 20:57:34	Template Konteks - kampanye digital marketing branding.txt	files/template_konteks_kampanye_digital_marketing_branding_1787752654.txt	1993	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
5c1e4069-7f73-403d-a60d-fdf672cd9b3a	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi2_1787754767.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:32:47	Template Konteks - pengembangan website aplikasi(2).txt	files/template_konteks_pengembangan_website_aplikasi2_1787754767.txt	1697	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
ba1ae4e2-6d49-45b2-a6ae-19ff07aef305	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi3_1787756340.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 21:59:00	Template Konteks - pengembangan website aplikasi(3).txt	files/template_konteks_pengembangan_website_aplikasi3_1787756340.txt	1030	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
44926279-9aa7-4289-a643-274844741574	\N	260cc0d8-81b0-453d-91b8-67d9ca92878a	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi4_1787758002.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 22:26:42	Template Konteks - pengembangan website aplikasi(4).txt	files/template_konteks_pengembangan_website_aplikasi4_1787758002.txt	961	txt	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1
f66d8ce7-f6c2-45d3-a4c8-45dd8c351483	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/template_konteks_pengembangan_website_aplikasi_1787842522.txt	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-27 21:55:22	Template Konteks - pengembangan website aplikasi.txt	files/template_konteks_pengembangan_website_aplikasi_1787842522.txt	961	txt	05ff871f-a9e4-4603-bcbc-91ce388566d8
1c02a014-e2ff-4911-9cff-3ab144b9810e	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pitch Deck Koladi.pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 20:55:04	Pitch Deck Koladi.pdf	files/Pitch Deck Koladi.pdf	22437379	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
f3aaedc7-054c-4426-a998-12b6bd80fdfe	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Pitch Deck Koladi(1).pdf	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 20:55:57	Pitch Deck Koladi(1).pdf	files/Pitch Deck Koladi(1).pdf	22437379	pdf	05ff871f-a9e4-4603-bcbc-91ce388566d8
1a4aae62-ec18-4983-bc1b-8a16023f275c	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/Lampiran_Koladi_Rapi.docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 20:56:20	Lampiran_Koladi_Rapi.docx	files/Lampiran_Koladi_Rapi.docx	28613	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
cf964d18-8bc9-419c-9750-212ce85f24eb	\N	5f1cb50a-2512-48f4-a184-e06a4665ef49	http://localhost:8000/storage/files/tanscript.docx	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-09-12 21:07:25	tanscript.docx	files/tanscript.docx	63086	docx	05ff871f-a9e4-4603-bcbc-91ce388566d8
\.


--
-- TOC entry 5578 (class 0 OID 28595)
-- Dependencies: 239
-- Data for Name: folders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.folders (id, workspace_id, name, is_private, created_by, created_at, updated_at, deleted_at, parent_id, company_id) FROM stdin;
80501ac6-ae7b-4ddf-a77b-bcfa7cdbb9cb	04d422ef-974e-4f86-a430-d0bfec6f29a3	Batu Aji	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 22:51:31	2026-07-11 22:51:31	\N	\N	\N
5e604d71-3411-4c9b-b580-534f2e2c4148	\N	Batu Aji	f	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:08:50	2026-07-16 18:08:50	\N	\N	05ff871f-a9e4-4603-bcbc-91ce388566d8
\.


--
-- TOC entry 5579 (class 0 OID 28604)
-- Dependencies: 240
-- Data for Name: insight_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insight_recipients (id, insight_id, user_id) FROM stdin;
\.


--
-- TOC entry 5580 (class 0 OID 28609)
-- Dependencies: 241
-- Data for Name: insights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insights (id, workspace_id, created_by, description, delivery_days, delivery_time, is_private, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5581 (class 0 OID 28619)
-- Dependencies: 242
-- Data for Name: invitations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invitations (id, email_target, token, status, invited_by, company_id, created_at, expired_at, updated_at) FROM stdin;
cc551819-7fe7-4acc-bfa8-7e23666eddcf	kuliahbisa2005@gmail.com	x84UkOR4ylqn1XWWhYm8NCrW8LJYz7BoQQBckgKALEa1o28n2DDlEeGiuX15SQy2	accepted	019f5015-b2cc-7144-82fa-fac0594f4e9b	05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-14 22:23:04	2026-07-17 22:23:04	2026-07-14 22:24:05.328883
62396d40-d8b9-46f4-910b-b80976a9f186	rendyfoto10@gmail.com	K8Tsjk8kUORE3laYno5XLVLZMrXPfMzbtsEgw7zXnvXVQvUmVqbuvGVtyRsVmhkl	accepted	019f5015-b2cc-7144-82fa-fac0594f4e9b	05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-16 17:47:35	2026-07-19 17:47:35	2026-07-16 17:54:02.489269
a9666fd0-00c9-4b94-8cee-b3da8f8ff411	rendifoto10@gmail.com	cWwMZIA5xVuCVVKBUv1bGx9dVeBaoF2zlLvOo0QBA6oF5nP9R0Rh3nKJFmkmxTXU	pending	019f5015-b2cc-7144-82fa-fac0594f4e9b	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1	2026-08-26 21:19:30	2026-08-29 21:19:30	2026-08-26 21:19:30
210f37d6-ff0d-438d-b342-71b97ee522b2	kuliahbisa2005@gmail.com	CfI46gkU92VZKO2Fzn9uv0wvjTRVlCPstqdjPDGWjYiy3jL5sduvYucE7chUfGS5	accepted	019f5015-b2cc-7144-82fa-fac0594f4e9b	05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-08-27 13:30:53	2026-08-30 13:30:53	2026-08-27 13:32:02.608667
\.


--
-- TOC entry 5582 (class 0 OID 28631)
-- Dependencies: 243
-- Data for Name: labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.labels (id, name, color_id, created_at, updated_at, workspace_id) FROM stdin;
\.


--
-- TOC entry 5583 (class 0 OID 28641)
-- Dependencies: 244
-- Data for Name: leave_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leave_requests (id, user_id, workspace_id, leave_type, start_date, end_date, reason, status, approved_by, attachment_url, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5584 (class 0 OID 28651)
-- Dependencies: 245
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, sender_id, content, message_type, reply_to_message_id, is_edited, edited_at, deleted_at, created_at, is_read, read_at, updated_at) FROM stdin;
bcaecb7b-9b02-47eb-a0d5-a895187b4b9e	30766506-f241-4acb-95bc-673c3885ad0b	019f5015-b2cc-7144-82fa-fac0594f4e9b	halo	text	\N	f	\N	\N	2026-07-16 18:07:03	f	\N	2026-07-16 18:07:03
\.


--
-- TOC entry 5585 (class 0 OID 28661)
-- Dependencies: 246
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
3	2025_11_27_204411_create_subscription_tables	1
4	2025_11_30_205745_add_system_role_id_to_users_table	2
5	2025_12_20_180641_create_notifications_table	3
6	2025_12_23_110225_add_status_active_to_user_companies_table	4
7	2026_03_16_004805_add_workspace_id_to_labels_table	5
8	2026_08_22_150000_create_ai_processing_logs_table	6
\.


--
-- TOC entry 5587 (class 0 OID 28668)
-- Dependencies: 248
-- Data for Name: mindmap_nodes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmap_nodes (id, mindmap_id, parent_id, title, description, type, x_position, y_position, connection_side, sort_order, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5588 (class 0 OID 28684)
-- Dependencies: 249
-- Data for Name: mindmaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmaps (id, workspace_id, title, description, created_at, updated_at) FROM stdin;
4bff77c0-3099-4ace-bc1e-c61234c06fdf	04d422ef-974e-4f86-a430-d0bfec6f29a3	Mind Map koladi	Mind map untuk workspace koladi	2026-07-15 17:33:37	2026-07-15 17:33:37
2d1a6143-afac-4bc8-a8d8-8688573b2250	5f1cb50a-2512-48f4-a184-e06a4665ef49	Mind Map Marketing	Mind map untuk workspace Marketing	2026-07-16 18:12:42	2026-07-16 18:12:42
\.


--
-- TOC entry 5589 (class 0 OID 28696)
-- Dependencies: 250
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, company_id, workspace_id, type, title, message, context, notifiable_type, notifiable_id, actor_id, is_read, read_at, action_url, created_at, updated_at) FROM stdin;
019f6a97-3cc9-7144-954b-3158b5e5d86d	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	task	Tugas baru ditugaskan	Membuat Kalender Konten Instagram Agustus 2026	Content Planning · Marketing	App\\Models\\Task	9407f8ee-269b-43fe-9fec-4daac7d604df	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/kanban-tugas/5f1cb50a-2512-48f4-a184-e06a4665ef49?task=9407f8ee-269b-43fe-9fec-4daac7d604df	2026-07-16 18:02:04	2026-07-16 18:02:04
019f6a98-af15-7399-a21d-ef9832cb48ab	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	task	Tugas baru ditugaskan	Desain Carousel Edukasi "5 Tips Meningkatkan Produktivitas Tim"	Content Production · Marketing	App\\Models\\Task	6c2712e8-6a10-48f9-a262-61e4846be4f9	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/kanban-tugas/5f1cb50a-2512-48f4-a184-e06a4665ef49?task=6c2712e8-6a10-48f9-a262-61e4846be4f9	2026-07-16 18:03:39	2026-07-16 18:03:39
019f6a99-3a85-729f-9325-ebad5173c848	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	task	Tugas baru ditugaskan	Menulis Caption & Hashtag untuk Konten Mingguan	Copywriting · Marketing	App\\Models\\Task	41e17448-a1a7-4d35-834f-dd3e7f3bf423	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/kanban-tugas/5f1cb50a-2512-48f4-a184-e06a4665ef49?task=41e17448-a1a7-4d35-834f-dd3e7f3bf423	2026-07-16 18:04:14	2026-07-16 18:04:14
019f6a9a-44e3-7191-81ab-3ddc83b41d7e	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	task	Tugas baru ditugaskan	Produksi Video Reels Promosi Fitur Koladi AI	Content Production · Marketing	App\\Models\\Task	32fcff78-39fc-4452-a773-d8e3893d4c5d	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/kanban-tugas/5f1cb50a-2512-48f4-a184-e06a4665ef49?task=32fcff78-39fc-4452-a773-d8e3893d4c5d	2026-07-16 18:05:22	2026-07-16 18:05:22
019f6a9b-39df-70a4-a43b-7e6119407ea6	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	announcement	Pengumuman baru	📢 Kick-off Campaign Marketing Q3 2026	Pengumuman Perusahaan	App\\Models\\Pengumuman	f7d68bac-fdd2-4b15-a4fe-71a59925c824	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/companies/05ff871f-a9e4-4603-bcbc-91ce388566d8/pengumuman-perusahaan/f7d68bac-fdd2-4b15-a4fe-71a59925c824	2026-07-16 18:06:25	2026-07-16 18:06:25
019f6a9b-3c86-7326-b77b-6dd4ad88aca3	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	announcement	Pengumuman baru	📢 Kick-off Campaign Marketing Q3 2026	Pengumuman Perusahaan	App\\Models\\Pengumuman	f7d68bac-fdd2-4b15-a4fe-71a59925c824	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/companies/05ff871f-a9e4-4603-bcbc-91ce388566d8/pengumuman-perusahaan/f7d68bac-fdd2-4b15-a4fe-71a59925c824	2026-07-16 18:06:26	2026-07-16 18:06:26
019f6a9b-ce07-71f3-aa74-da9e1df569c5	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	chat	Pesan baru dari Rendi Sinaga	halo	Chat Perusahaan	App\\Models\\Message	bcaecb7b-9b02-47eb-a0d5-a895187b4b9e	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/company/05ff871f-a9e4-4603-bcbc-91ce388566d8/chat	2026-07-16 18:07:03	2026-07-16 18:07:03
019f6a9c-929e-71c2-9bb9-a1bfd69449d1	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	schedule	Jadwal meeting baru	Rapat Bersama	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019f6a9c-9284-73af-8c3a-abd77768c7b9	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/jadwal-umum/019f6a9c-9284-73af-8c3a-abd77768c7b9?company_id=05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-16 18:07:53	2026-07-16 18:07:53
019f6a9c-954f-73f5-bbf2-b35cee3d8767	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	schedule	Jadwal meeting baru	Rapat Bersama	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019f6a9c-9284-73af-8c3a-abd77768c7b9	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/jadwal-umum/019f6a9c-9284-73af-8c3a-abd77768c7b9?company_id=05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-16 18:07:54	2026-07-16 18:07:54
019f6a9d-2319-72f0-bb0d-9de11d6cee7a	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	schedule	Jadwal meeting baru	Rapat dept it	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019f6a9d-22f9-721b-9296-47b0aae7dc16	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/jadwal-umum/019f6a9d-22f9-721b-9296-47b0aae7dc16?company_id=05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-16 18:08:30	2026-07-16 18:08:30
019f6a9d-258c-728a-a799-c1139d131f2c	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	\N	schedule	Jadwal meeting baru	Rapat dept it	Jadwal Umum Perusahaan	App\\Models\\CalendarEvent	019f6a9d-22f9-721b-9296-47b0aae7dc16	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/jadwal-umum/019f6a9d-22f9-721b-9296-47b0aae7dc16?company_id=05ff871f-a9e4-4603-bcbc-91ce388566d8	2026-07-16 18:08:31	2026-07-16 18:08:31
01a095f2-6305-71b9-8d0a-53df09840731	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Menggunakan Gemini API sebagai engine AI utama untuk pemrosesan brief	Marketing	App\\Models\\Decision	6d9641ae-e731-43bb-809f-ea2c07cff9db	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:05	2026-09-12 21:08:05
01a095f2-6b24-70f9-8186-f29d232c5386	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Menggunakan Gemini API sebagai engine AI utama untuk pemrosesan brief	Marketing	App\\Models\\Decision	6d9641ae-e731-43bb-809f-ea2c07cff9db	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6b83-72b1-bffe-bfbd59f65415	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Mengubah strategi harga dengan meniadakan opsi gratis selamanya (langsung berbayar atau trial 7 hari lalu wajib bayar)	Marketing	App\\Models\\Decision	c5242d6a-4ca8-4fb4-b650-4e313ae17f38	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6bc9-703e-b7dc-1623f9495f9f	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Mengubah strategi harga dengan meniadakan opsi gratis selamanya (langsung berbayar atau trial 7 hari lalu wajib bayar)	Marketing	App\\Models\\Decision	c5242d6a-4ca8-4fb4-b650-4e313ae17f38	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6c15-73c8-81b0-4267237bd6f3	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Menata ulang urutan slide pitch deck dengan menaruh risiko keamanan data dan privasi di bagian depan	Marketing	App\\Models\\Decision	ca233be1-e83a-4675-90bc-0a6b23bc75ef	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6c64-72cc-a6d9-828b17f4cd08	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Menata ulang urutan slide pitch deck dengan menaruh risiko keamanan data dan privasi di bagian depan	Marketing	App\\Models\\Decision	ca233be1-e83a-4675-90bc-0a6b23bc75ef	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6cb3-72b9-9727-b668481345e2	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Mengubah visualisasi diagram alur teknologi dari bentuk tree menjadi circle/loop proses	Marketing	App\\Models\\Decision	8e8e7586-c7ce-4f4a-a577-c0b55776fc54	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
01a095f2-6ced-7390-b33a-605b8df457cf	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	5f1cb50a-2512-48f4-a184-e06a4665ef49	announcement	Keputusan baru ditetapkan	Mengubah visualisasi diagram alur teknologi dari bentuk tree menjadi circle/loop proses	Marketing	App\\Models\\Decision	8e8e7586-c7ce-4f4a-a577-c0b55776fc54	019f5015-b2cc-7144-82fa-fac0594f4e9b	f	\N	http://localhost:8000/activity-log/5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-09-12 21:08:07	2026-09-12 21:08:07
\.


--
-- TOC entry 5590 (class 0 OID 28712)
-- Dependencies: 251
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, email, otp, type, expires_at, is_used, created_at, updated_at) FROM stdin;
1	kuliahbisa2005@gmail.com	864418	register	2026-06-21 20:52:16	t	2026-06-21 20:42:16	2026-06-21 20:42:36
2	rendyfoto10@gmail.com	736608	register	2026-07-16 18:03:04	t	2026-07-16 17:53:04	2026-07-16 17:53:54
3	kuliahbisa2005@gmail.com	018673	reset_password	2026-08-27 13:42:58	t	2026-08-27 13:32:58	2026-08-27 13:33:52
\.


--
-- TOC entry 5592 (class 0 OID 28724)
-- Dependencies: 253
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.plans (id, plan_name, price_monthly, base_user_limit, description, is_active, created_at, updated_at) FROM stdin;
d1d863e5-c96b-49d7-8107-0df22885fe9d	Paket Basic	15000.00	5	Auto seeded	t	2026-06-21 20:35:42	2026-06-21 20:35:42
a505a405-35dd-4f10-8eb8-310075e2121d	Paket Standard	45000.00	20	Auto seeded	t	2026-06-21 20:35:42	2026-06-21 20:35:42
106b6b12-1855-4eaa-8c2d-0ca2cc483af3	Paket Business	100000.00	50	Auto seeded	t	2026-06-21 20:35:42	2026-06-21 20:35:42
\.


--
-- TOC entry 5593 (class 0 OID 28736)
-- Dependencies: 254
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name) FROM stdin;
11111111-1111-1111-1111-111111111111	SuperAdmin
a688ef38-3030-45cb-9a4d-0407605bc322	Manager
33333333-3333-3333-3333-333333333333	AdminSistem
ed81bd39-9041-43b8-a504-bf743b5c2919	Member
55555555-5555-5555-5555-555555555555	Administrator
\.


--
-- TOC entry 5594 (class 0 OID 28742)
-- Dependencies: 255
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
M0irbLljhUYUUSfN4yJuQsPMNNbU7tMIftaSBEaS	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaVRLcnZLOHpGSE1UUU5uUlF5WnR0SFdSN1FEdm5KdFgycHFCSjM4TiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7czo1OiJyb3V0ZSI7czoxMToibGFuZGluZ3BhZ2UiO319	1789222825
T5yuVUEnVFj03E4wOEjLTRMcbRQfJipZVrhV4I2T	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.137.0 Chrome/148.0.7778.280 Electron/42.10.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiaHVObkVjVHQ0OExEb2RtY1Voc0djamdQTlhkbjd0R0ZzanJnd1V2dSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czoxMToibGFuZGluZ3BhZ2UiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1789218839
ny6iuSDz6AvjloHFYXBH94sTHvdPUIqmAgq36BiP	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoieGltTjg5YkNXMDhESGlkYldCYnV0ZnVHeEs0azdnbVJsQnFkN1QyVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hdXRoL2dvb2dsZSI7czo1OiJyb3V0ZSI7czoxMjoiZ29vZ2xlLmxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1OiJzdGF0ZSI7czo0MDoib1dkdkczblkwWGVyTjF6ekFKMjhOME92bkhvSXd3NEhTbld3Z21UZyI7fQ==	1789218590
\.


--
-- TOC entry 5595 (class 0 OID 28750)
-- Dependencies: 256
-- Data for Name: subscription_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscription_invoices (id, subscription_id, external_id, payment_url, amount, billing_month, status, paid_at, payment_details, created_at, updated_at, payment_method, proof_of_payment, admin_notes, verified_at, verified_by, payer_name, payer_bank, payer_account_number) FROM stdin;
01a04166-8a73-7317-a0e0-47b3c0615e2a	01a04166-8a64-7388-8812-b998f5e3f975	INV-1787803634-519	\N	100000.00	2026-08	paid	2026-08-27 11:07:56	{"plan_id":"106b6b12-1855-4eaa-8c2d-0ca2cc483af3","plan_name":"Paket Business","new_addon_count":0,"new_total_limit":50,"is_downgrade":false}	2026-08-27 11:07:14	2026-08-27 11:07:56	manual	payment_proofs/proof_01a04166-8a73-7317-a0e0-47b3c0615e2a_1787803661.png	Disetujui oleh admin	2026-08-27 11:07:56	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	aa	BCA	12341234124
\.


--
-- TOC entry 5596 (class 0 OID 28765)
-- Dependencies: 257
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscriptions (id, company_id, plan_id, addons_user_count, total_user_limit, start_date, end_date, status, created_at, updated_at, deleted_at) FROM stdin;
01a04166-8a64-7388-8812-b998f5e3f975	05ff871f-a9e4-4603-bcbc-91ce388566d8	106b6b12-1855-4eaa-8c2d-0ca2cc483af3	0	50	2026-08-27 11:07:14	2026-09-27 11:07:56	active	2026-08-27 11:07:14	2026-08-27 11:07:56	\N
\.


--
-- TOC entry 5605 (class 0 OID 37508)
-- Dependencies: 266
-- Data for Name: task_activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_activities (id, workspace_id, task_id, user_id, task_title, action_type, old_column, new_column, created_at, updated_at) FROM stdin;
21469fe5-38bc-498c-aea0-d9007927c01c	04d422ef-974e-4f86-a430-d0bfec6f29a3	7d7e5177-b819-4474-8a73-ed1cc2ef58d7	019f5015-b2cc-7144-82fa-fac0594f4e9b	abc	moved	Dikerjakan	Selesai	2026-07-13 17:15:30	2026-07-13 17:15:30
8311c4a2-8a90-4969-8799-caccd2da25d1	04d422ef-974e-4f86-a430-d0bfec6f29a3	cd072194-9549-4b4a-a487-8267f170bb69	019f5015-b2cc-7144-82fa-fac0594f4e9b	asdad	moved	Dikerjakan	Batal	2026-07-14 22:06:58	2026-07-14 22:06:58
083b299e-fc81-4024-a883-86ce2782e261	04d422ef-974e-4f86-a430-d0bfec6f29a3	d2f05d38-64de-4fb8-92e5-b44b2a73489e	019f5015-b2cc-7144-82fa-fac0594f4e9b	Membuat Video Submission 3 Menit	moved	To Do List	Dikerjakan	2026-07-15 17:12:45	2026-07-15 17:12:45
2a8bee18-efc9-484f-9a1b-7411313523f7	04d422ef-974e-4f86-a430-d0bfec6f29a3	6429c5e2-6e97-4fcc-963e-18034cd88758	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menyusun Roadmap Pengembangan Produk	moved	To Do List	Selesai	2026-07-15 17:36:20	2026-07-15 17:36:20
211c1ede-03c1-4bf4-953a-8cb209fc7401	04d422ef-974e-4f86-a430-d0bfec6f29a3	151504a3-7a93-4373-b115-eb048a9684e5	019f5015-b2cc-7144-82fa-fac0594f4e9b	Melakukan Usability Testing (UT) pada Prototipe	moved	To Do List	Selesai	2026-07-15 17:36:21	2026-07-15 17:36:21
\.


--
-- TOC entry 5597 (class 0 OID 28778)
-- Dependencies: 258
-- Data for Name: task_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_assignments (id, task_id, user_id, assigned_at) FROM stdin;
fa20b624-df01-4615-aefe-f98db95e798c	87fa3895-44c8-4600-b6dc-29cd06e1a07b	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46
bcbf3ba7-1435-4ba9-82a1-d0e79cee2c2f	6c2712e8-6a10-48f9-a262-61e4846be4f9	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-16 18:13:13
1ecfb56c-8417-4060-bf27-54d5bf2e82dc	32fcff78-39fc-4452-a773-d8e3893d4c5d	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-16 18:13:27
\.


--
-- TOC entry 5598 (class 0 OID 28784)
-- Dependencies: 259
-- Data for Name: task_labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_labels (task_id, label_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5599 (class 0 OID 28789)
-- Dependencies: 260
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tasks (id, workspace_id, created_by, title, description, status, board_column_id, priority, is_secret, start_datetime, due_datetime, created_at, updated_at, deleted_at, phase, completed_at) FROM stdin;
bd92e76f-9fe3-4eea-9cc4-6aab4ad6bd1f	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Task Uji Postman TC-TASK-02	Ini adalah deskripsi task pengujian untuk TC-TASK-02.	inprogress	7242fcfb-27ef-4dc7-a93f-7eaab996f031	high	f	2025-06-01 08:00:00	2025-06-30 17:00:00	2026-06-21 21:41:54	2026-06-21 22:29:14	2026-06-21 22:29:14	\N	\N
0f6d1953-9513-4d0b-a2ac-37889caa8ade	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Task Uji Postman TC-TASK-02	Ini adalah deskripsi task pengujian untuk TC-TASK-02.	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2025-06-01 08:00:00	2025-06-30 17:00:00	2026-06-21 21:35:00	2026-06-21 22:53:15	2026-06-21 22:53:15	\N	\N
eecc9db0-d681-4caf-b1cb-b1e7616f19a3	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Design UI Homepage	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-23 18:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
c3abad23-5c98-4e8a-bb5d-fc2978526758	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Fix Login Bug	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-24 01:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
b0e90479-ee5e-4100-b25b-1724571aa45f	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Setup Database Migration	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-24 15:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
d78227a7-cab9-4ecf-a3cd-52c4ab476bdd	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Integrasi API Payment	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-25 15:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
f4876fac-2dc7-4243-bbec-8820837487d2	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Testing Fitur Chat	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-27 15:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
4117da23-0611-477a-aa0e-db1287003909	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Deploy ke Server	Task untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-23 15:04:29	2026-06-30 15:02:39	2026-06-23 15:04:29	2026-06-23 15:04:29	\N	\N	\N
8ea20bec-a467-4d73-8046-05f7d149d11b	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Deploy Hotfix	Task urgent untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-23 12:06:58	2026-06-23 14:06:58	2026-06-23 15:06:58	2026-06-23 15:06:58	\N	\N	\N
2e6bfd7f-38e3-4591-8338-aff978a09cd4	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Update Dokumentasi	Task urgent untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-23 12:06:58	2026-06-23 16:06:58	2026-06-23 15:06:58	2026-06-23 15:06:58	\N	\N	\N
e6fab89e-0284-48b5-9894-78d79acf2c8b	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Code Review PR	Task urgent untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-23 12:06:58	2026-06-23 17:06:58	2026-06-23 15:06:58	2026-06-23 15:06:58	\N	\N	\N
0a85cef5-d960-40da-b7e9-2c8d7df5b93a	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Meeting Standup	Task urgent untuk pengujian EDF scheduling	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-23 12:06:58	2026-06-23 18:06:58	2026-06-23 15:06:58	2026-06-23 15:06:58	\N	\N	\N
40cc34c7-0b37-43f5-ad38-be23273129b5	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Kirim Laporan Mingguan	Task urgent untuk pengujian EDF scheduling	inprogress	7242fcfb-27ef-4dc7-a93f-7eaab996f031	high	f	2026-06-23 12:06:58	2026-06-23 20:06:58	2026-06-23 15:06:58	2026-06-26 14:56:46	\N	\N	\N
2b7089e4-282a-4c0f-af37-256b9080c79b	3505e3a5-936a-4177-94bf-e8b59bbb7e20	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Urgent: Fix Critical Bug	Task urgent untuk pengujian EDF scheduling	inprogress	7242fcfb-27ef-4dc7-a93f-7eaab996f031	high	f	2026-06-23 12:06:58	2026-06-23 13:06:58	2026-06-23 15:06:58	2026-06-26 14:56:57	\N	\N	\N
9709abfd-b686-4ce9-9192-516546495a91	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Fix Authentication Bug	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	urgent	f	2026-06-30 13:25:21	2026-06-30 10:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
3e94fa6b-e287-4cde-870b-ac7884a5e54f	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Deploy Hotfix Production	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	urgent	f	2026-06-30 13:25:21	2026-06-30 12:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
a7443e73-3c5e-4d11-b941-7a9a74c3f2a6	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Update SSL Certificate	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-30 13:25:21	2026-06-30 14:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
64469dc3-94ef-41da-b658-d4311c652b25	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Code Review Pull Request	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-30 13:25:21	2026-06-30 18:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
5ee88a1d-a505-4b65-ad97-9da89d8b3a2c	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Submit Laporan Sprint	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-30 13:25:21	2026-06-30 21:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
53939d64-5b25-428e-86af-70b51930955f	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Meeting Client Presentation	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	high	f	2026-06-30 13:25:21	2026-07-01 01:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
6590abf8-7d1a-4a06-8194-3825a0b654da	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Testing Fitur Payment	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-30 13:25:21	2026-07-01 09:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
7f9468b9-b589-41c3-be5e-a4fc90c76d06	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Integrasi API Xendit	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-30 13:25:21	2026-07-01 21:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
0307d584-b0ee-4b4a-adfb-30fa847b122a	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Refactor Database Schema	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-30 13:25:21	2026-07-02 15:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
93bca3ec-37ac-4b79-a164-fc3f5c5b428a	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Setup CI/CD Pipeline	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	medium	f	2026-06-30 13:25:21	2026-07-03 03:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
5f639815-1f9e-412a-bc72-614f0a27adbf	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Buat Dokumentasi API	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	low	f	2026-06-30 13:25:21	2026-07-05 15:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
7ea3d4b8-1fe1-40f8-8190-a917a4540e73	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Design Landing Page Baru	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	low	f	2026-06-30 13:25:21	2026-07-07 15:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
19353e4c-c898-47e7-92cd-140983a4e9fb	3505e3a5-936a-4177-94bf-e8b59bbb7e20	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Update Dependencies	Task untuk pengujian EDF scheduling di Koladi	todo	13cc46b5-96bc-469b-b245-9fa1ec7fc731	low	f	2026-06-30 13:25:21	2026-07-10 15:25:21	2026-06-30 15:25:21	2026-06-30 15:25:21	\N	\N	\N
6e205b50-3c8d-41c4-9133-951cd8c31e72	3e26510b-3f68-417e-bcb1-61cae095bec0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Conduct Usability Testing	Perform usability testing on the Figma design or website with real users to gather validation and testimonials for the jury.	todo	68ab1be9-d6c9-4cf9-9123-cf846372aaef	high	f	\N	\N	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	\N	\N
cf0bdc18-52ce-45ff-a875-b7641d43c363	3e26510b-3f68-417e-bcb1-61cae095bec0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Define Roadmap and Team Composition	Detail the development roadmap, current progress stage, and specific roles/strengths of the team leader and members in the proposal.	todo	68ab1be9-d6c9-4cf9-9123-cf846372aaef	high	f	\N	\N	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	\N	\N
33fde980-716a-4c38-8519-9980b4519c87	3e26510b-3f68-417e-bcb1-61cae095bec0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Create Video Submission	Produce a maximum 3-minute video combining a 1-minute investor pitch and a user demo. Ensure content accuracy if using AI tools.	todo	68ab1be9-d6c9-4cf9-9123-cf846372aaef	medium	f	\N	\N	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	\N	\N
87fa3895-44c8-4600-b6dc-29cd06e1a07b	3e26510b-3f68-417e-bcb1-61cae095bec0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Submit Proposal Answers	Fill out and submit the mandatory Q&A form on the official platform (video.id / pidi.id).	todo	68ab1be9-d6c9-4cf9-9123-cf846372aaef	urgent	f	\N	\N	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	\N	\N
ba24a55b-d55a-4511-b66f-d31156aed938	3e26510b-3f68-417e-bcb1-61cae095bec0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	asdasdad asd as	asdasd asdsad	todo	68ab1be9-d6c9-4cf9-9123-cf846372aaef	medium	f	\N	\N	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	\N	\N
6472b59b-cd58-4697-a423-5761523a2964	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	asdad	<p>dasda</p>	inprogress	a33ed0dd-e70d-4470-be68-ffebf0ec97d9	medium	f	2026-07-11 22:29:00	2026-07-31 00:29:00	2026-07-11 22:29:52	2026-07-13 15:06:52	\N	adas	\N
7d7e5177-b819-4474-8a73-ed1cc2ef58d7	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	abc	<p>asd</p>	done	5162de8d-d199-4cd7-a6a2-2aeb7e59bf18	medium	f	2026-07-18 14:35:00	2026-07-25 14:35:00	2026-07-11 14:35:18	2026-07-13 17:15:28	\N	\N	2026-07-13 17:15:28+07
cd072194-9549-4b4a-a487-8267f170bb69	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	asdad	<p>dasda</p>	cancel	15a4a18f-22b5-4514-8f4c-ac83ae105068	medium	f	2026-07-11 22:29:00	2026-07-31 00:29:00	2026-07-11 22:29:52	2026-07-14 22:06:57	\N	adas	\N
6582d5f1-29fe-42a2-b172-0f412e300482	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menyusun Komposisi dan Peran Tim (Team Composition)	Mendefinisikan peran ketua dan anggota secara spesifik dalam pengembangan produk serta menonjolkan keunggulan atau portofolio masing-masing anggota.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58	\N	\N	\N
9297734a-0fe1-417a-9f83-b0dea6af2fc1	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Validasi Problem-Solution Fit	Memastikan solusi teknologi yang diajukan benar-benar menjawab akar permasalahan pengguna dan menghindari lompatan teknologi tanpa kejelasan masalah (jumping to technology).	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58	\N	\N	\N
eb1bd885-d6ca-4fc5-bf9e-5bdae31e1282	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menyusun Strategi Kemitraan B2G (Business to Government)	Mengidentifikasi touchpoint dengan pemerintah daerah atau pusat melalui partisipasi seminar/webinar dan menentukan positioning produk dalam ekosistem kemitraan.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:02:58	\N	\N	\N
d2f05d38-64de-4fb8-92e5-b44b2a73489e	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Membuat Video Submission 3 Menit	Memproduksi video pitching dengan ketentuan menit pertama fokus pada pitching ke investor/off-taker, dan sisa durasi digunakan untuk demo penggunaan produk bagi calon pengguna.	inprogress	a33ed0dd-e70d-4470-be68-ffebf0ec97d9	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:12:45	\N	\N	\N
6966fb70-9cc5-47f4-aa06-12fb3d9b8aa6	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Analisis Sistem Berjalan dan Atribut Tugas Koladi	Melakukan peninjauan kode pada TaskController.php dan dokumentasi sistem Koladi untuk memetakan atribut tugas seperti created_at, due_datetime, dan status.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
5326a931-65db-4d81-bb0a-ad48ccfa6654	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Perancangan Algoritma Non-Preemptive EDF	Merancang aturan penjadwalan EDF non-preemptive dan skenario simulasi berdasarkan tingkat beban kerja dan kerapatan tenggat waktu.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
aa2ced60-1d02-4b3a-9ac3-1ce91a935dbd	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Implementasi Skrip Simulasi Berbasis Python	Mengembangkan skrip buat_dataset.py, simulasi_penjadwalan.py, dan agregasi_hasil.py untuk menguji performa algoritma secara terisolasi.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
f89bab5c-8d4d-45af-acd0-0fc450e5566f	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Implementasi Backend Laravel dan Endpoint Penjadwalan	Memodifikasi getKanbanTasks() di TaskController.php untuk mengurutkan tugas berdasarkan due_datetime dan membuat endpoint getEdfSchedule() untuk membandingkan FCFS dan EDF.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
cc67f571-ce8e-439d-975a-6fe2516fe0aa	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Pengembangan Komponen Visual Antarmuka Kanban	Mengintegrasikan indikator urgensi visual berupa badge warna dan aksen border kiri pada kartu tugas di papan Kanban berdasarkan sisa waktu tenggat.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
5710a09e-4177-4aaa-b13f-176f3b63b697	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Evaluasi Kuantitatif dan Pengujian Statistik	Melakukan pengujian hipotesis menggunakan Uji Wilcoxon Signed-Rank untuk mengukur perbedaan Mean Tardiness dan Number of Late Tasks antara EDF dan FCFS.	todo	f38bdbfc-3979-4f73-83c7-d8cd4a7926ce	medium	f	\N	\N	2026-07-15 17:15:25	2026-07-15 17:15:25	\N	\N	\N
6429c5e2-6e97-4fcc-963e-18034cd88758	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menyusun Roadmap Pengembangan Produk	Membuat peta jalan pengembangan solusi yang jelas dan eksplisit untuk menunjukkan tahapan kesiapan inovasi (Innovation Readiness Level) kepada dewan juri.	done	5162de8d-d199-4cd7-a6a2-2aeb7e59bf18	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:36:20	\N	\N	2026-07-15 17:36:20+07
151504a3-7a93-4373-b115-eb048a9684e5	04d422ef-974e-4f86-a430-d0bfec6f29a3	019f5015-b2cc-7144-82fa-fac0594f4e9b	Melakukan Usability Testing (UT) pada Prototipe	Melakukan pengujian kegunaan mulai dari tingkat desain Figma, website, hingga pengujian langsung ke calon pengguna untuk mendapatkan validasi dan testimoni.	done	5162de8d-d199-4cd7-a6a2-2aeb7e59bf18	medium	f	\N	\N	2026-07-15 17:02:58	2026-07-15 17:36:21	\N	\N	2026-07-15 17:36:21+07
41e17448-a1a7-4d35-834f-dd3e7f3bf423	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Menulis Caption & Hashtag untuk Konten Mingguan	<p>Buat caption yang engaging untuk 10 postingan minggu ini. Sertakan CTA yang jelas serta riset hashtag yang relevan dengan target audiens bisnis digital dan UMKM.</p>	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	medium	f	2026-07-16 00:04:00	2026-07-27 00:04:00	2026-07-16 18:04:14	2026-07-16 18:04:14	\N	Copywriting	\N
9407f8ee-269b-43fe-9fec-4daac7d604df	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Membuat Kalender Konten Instagram Agustus 2026	<p>Menyusun kalender konten selama 1 bulan dengan minimal 20 postingan. Tentukan tema mingguan, objective, CTA, dan jadwal publikasi. Pastikan menyesuaikan campaign produk yang sedang berjalan.</p>	inprogress	3ffd2485-a10e-4e39-a713-c00a84c6ade4	medium	f	2026-07-16 18:01:00	2026-07-31 18:02:00	2026-07-16 18:02:04	2026-07-16 18:04:22	\N	Content Planning	\N
32fcff78-39fc-4452-a773-d8e3893d4c5d	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Produksi Video Reels Promosi Fitur Koladi AI	<p>Produksi video berdurasi 30–45 detik yang menampilkan fitur AI Brief Parser dan AI Project Assistant. Tambahkan subtitle, musik bebas lisensi, dan format vertikal 1080x1920.</p>	done	14abcdb6-4e1e-4809-a6b8-7e107a8f2d01	medium	f	2026-07-16 18:05:00	2026-07-27 20:05:00	2026-07-16 18:05:22	2026-07-16 18:14:11	\N	Content Production	2026-07-16 18:14:11+07
6c2712e8-6a10-48f9-a262-61e4846be4f9	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Desain Carousel Edukasi "5 Tips Meningkatkan Produktivitas Tim"	<p>Buat carousel Instagram sebanyak 6 slide menggunakan branding perusahaan. Siapkan file Canva/Figma dan export PNG kualitas tinggi untuk dijadwalkan posting.</p>	inprogress	3ffd2485-a10e-4e39-a713-c00a84c6ade4	medium	f	2026-07-16 19:03:00	2026-07-24 00:03:00	2026-07-16 18:03:38	2026-07-16 18:15:05	\N	Content Production	\N
8833a202-f06d-45b3-93ec-8459394827ff	878834d3-87ad-459f-91e7-95536d600c4c	019f5015-b2cc-7144-82fa-fac0594f4e9b	halo	<p>asd</p>	todo	c98588bd-b2b3-4221-930c-1dadb4a8cb64	medium	f	2026-08-23 21:31:00	2026-08-29 12:03:00	2026-08-23 19:36:09	2026-08-23 19:36:09	\N	aa	\N
ff619466-e71d-49fa-b4d8-ffa1466ea91b	878834d3-87ad-459f-91e7-95536d600c4c	019f5015-b2cc-7144-82fa-fac0594f4e9b	halo	<p>asd</p>	todo	c98588bd-b2b3-4221-930c-1dadb4a8cb64	medium	f	2026-08-23 21:31:00	2026-08-29 12:03:00	2026-08-23 19:36:10	2026-08-23 19:36:10	\N	aa	\N
031d5ec8-6939-404b-ae76-d5645eb537d1	878834d3-87ad-459f-91e7-95536d600c4c	019f5015-b2cc-7144-82fa-fac0594f4e9b	halo	<p>asd</p>	todo	c98588bd-b2b3-4221-930c-1dadb4a8cb64	medium	f	2026-08-23 21:31:00	2026-08-29 12:03:00	2026-08-23 19:36:10	2026-08-23 19:36:10	\N	aa	\N
d4b45637-aaa8-4e2a-a315-61d9458a1855	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	asd	<p>dasd</p>	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	2026-08-26 03:23:00	2026-08-29 12:03:00	2026-08-26 21:29:05	2026-08-26 21:29:05	\N	\N	\N
94d912ba-7cf9-4434-93ad-4f6e33a93303	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	asd	<p>dasd</p>	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	2026-08-26 03:23:00	2026-08-29 12:03:00	2026-08-26 21:29:05	2026-08-26 21:29:05	\N	\N	\N
fb64e79d-f028-43de-bbdd-8e33b0e6b13d	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Wawancara kebutuhan pengguna & dokumen SRS	Melakukan wawancara untuk mengumpulkan kebutuhan pengguna dan menyusun dokumen Spesifikasi Kebutuhan Perangkat Lunak (SRS).	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	2026-08-27 23:59:59	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
938fa42b-adf3-4f60-b9c7-4b832c05a7c2	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Perancangan arsitektur sistem	Merancang arsitektur sistem yang akan digunakan untuk pengembangan website dan aplikasi.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
2eab2d35-c551-4114-ad1a-94a303bc4853	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Desain High-Fidelity UI di Figma	Membuat desain antarmuka pengguna dengan tingkat presisi tinggi (High-Fidelity) menggunakan Figma.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
f2754c04-969a-48bd-80ed-ae4037fe22e8	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Setup database & REST API	Melakukan konfigurasi basis data dan membangun REST API untuk mendukung integrasi sistem.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
91efecda-bc77-43ac-b796-e92b080ab4e3	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Slicing antarmuka responsif	Menerjemahkan desain UI menjadi kode antarmuka yang responsif untuk berbagai perangkat.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
db7fc528-af0b-4fcf-ac99-2f9e9656ecb7	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	Integrasi fitur utama & notifikasi	Menggabungkan fitur-fitur utama sistem serta mengimplementasikan fungsi notifikasi.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
a9cb51b4-74c6-44ec-b6aa-349769cdc7ba	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	QA testing fungsional & perbaikan bug	Melakukan pengujian fungsional oleh tim Quality Assurance dan memperbaiki kesalahan (bug) yang ditemukan.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
897ceb38-ffa8-4029-8277-88553ad84630	260cc0d8-81b0-453d-91b8-67d9ca92878a	019f5015-b2cc-7144-82fa-fac0594f4e9b	UAT bersama tim & serah terima	Melaksanakan User Acceptance Testing (UAT) bersama tim klien dan melakukan serah terima hasil pekerjaan.	todo	06064bad-30ca-472c-8aa4-0792107e7b89	medium	f	\N	\N	2026-08-26 22:28:02	2026-08-26 22:28:02	\N	\N	\N
ed3ff075-f5ac-4192-8081-197f2c2270ce	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Revisi Pitch Deck Kolabi	Menyederhanakan slide problem/solusi, memindahkan slide risiko dan privasi data ke depan, memperbesar foto tim (zoom wajah), dan mengubah diagram teknologi menjadi bentuk loop.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Pitch Deck & Presentasi	\N
8db70425-613b-401d-9342-333c7b8c9ffb	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Analisis Diferensiasi Produk (PDB)	Menyusun keunggulan kompetitif Kolabi dibanding kompetitor seperti Slack, fokus pada penyelesaian masalah spesifik lokal dan efisiensi kerja, bukan sekadar harga murah.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Strategi Bisnis	\N
75d9df76-e087-4c80-b85a-6eaa1e69d513	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Penyusunan Simulasi Biaya dan Strategi Harga	Menghitung ulang biaya token AI per user, biaya server, operasional, serta merancang skema harga langganan (B2B/retail) yang sesuai dengan perilaku pengguna di Indonesia.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Strategi Bisnis	\N
9307289c-9f8c-4ab7-9dae-442f9252b32f	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Implementasi Progress Bar Pemrosesan AI	Menambahkan visualisasi progress bar atau estimasi waktu tunggu saat AI sedang memproses dokumen/transkrip agar pengguna tidak menganggap aplikasi lemot.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	medium	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Pengembangan Produk	\N
acb8dd0b-4459-46b0-ad94-6dbc47b317dc	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Klarifikasi Tanggal Pertemuan Offtaker	Mengonfirmasi tanggal pasti pertemuan pilot testing dengan Offtaker yang dijadwalkan antara hari Selasa atau Rabu.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Klarifikasi	\N
17f4fca8-4da9-4725-90c7-e3a70b236d61	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Klarifikasi Metrik Efektivitas Produk	Menentukan metrik atau KPI konkret (sebelum vs sesudah menggunakan Kolabi) untuk membuktikan peningkatan efisiensi kepada juri/investor.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Klarifikasi	\N
340c83e9-78c5-4243-86b5-65293ef99aa8	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Klarifikasi Kebijakan Privasi Data AI	Menyusun draf kebijakan privasi data (Privacy Policy) dan Master Service Agreement (MSA) untuk menjamin keamanan data proyek pengguna.	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Klarifikasi	\N
029e347a-fa28-49c9-9ba7-285fdb4a4e97	5f1cb50a-2512-48f4-a184-e06a4665ef49	019f5015-b2cc-7144-82fa-fac0594f4e9b	Klarifikasi Rencana Skalabilitas Sistem	Menyusun rencana teknis dan simulasi kapasitas server untuk menangani lonjakan pengguna (misal hingga 1000 user sekaligus).	todo	27bf7bf8-e489-4fcd-aede-8d9782c4c98c	high	f	\N	\N	2026-09-12 21:08:08	2026-09-12 21:08:08	\N	Klarifikasi	\N
\.


--
-- TOC entry 5600 (class 0 OID 28800)
-- Dependencies: 261
-- Data for Name: user_companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_companies (id, user_id, company_id, roles_id, created_at, updated_at, deleted_at, status_active) FROM stdin;
e68099a9-2269-4753-a1d7-53f167d72aeb	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	fc21fabc-e558-4d71-b68e-616e6bdadb73	11111111-1111-1111-1111-111111111111	2026-06-21 20:42:49	2026-06-21 20:42:49	\N	t
16fb04c7-14bd-412f-8a29-a22ea85b475b	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	5c8f25d9-540f-4673-a788-424129bde7df	11111111-1111-1111-1111-111111111111	2026-06-28 21:31:29	2026-06-28 21:31:29	\N	t
91c434df-2f0e-446c-b4de-d77f31d4d492	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	f263faec-5824-4c51-b4b3-6bdd001ab84f	11111111-1111-1111-1111-111111111111	2026-06-30 15:10:01	2026-06-30 15:10:01	\N	t
5d0edaa9-3559-4a4f-a419-f844aa3529fe	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	c6eba6e5-5e74-4523-9063-40cae1888779	11111111-1111-1111-1111-111111111111	2026-06-30 15:10:02	2026-06-30 15:10:02	\N	t
a6c9e67a-c84f-4e22-8cfc-6bcc2fbef9c7	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	c785040c-a4cc-4be8-a752-8a5ee215dc72	11111111-1111-1111-1111-111111111111	2026-07-10 22:32:55	2026-07-10 22:32:55	\N	t
8863d7b1-b6a2-427c-8f4b-0260c764b23e	019f5015-b2cc-7144-82fa-fac0594f4e9b	05ff871f-a9e4-4603-bcbc-91ce388566d8	11111111-1111-1111-1111-111111111111	2026-07-11 14:30:33	2026-07-11 14:30:33	\N	t
7e96f376-b1d6-4153-90cb-4ea99f92253d	019f6a8f-c28f-7090-97d5-113e5d166b06	05ff871f-a9e4-4603-bcbc-91ce388566d8	a688ef38-3030-45cb-9a4d-0407605bc322	2026-07-16 17:54:02	2026-07-16 18:10:52	\N	t
f34ebe6a-08bb-4aa7-83fd-7c40ffd3e7e8	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	05ff871f-a9e4-4603-bcbc-91ce388566d8	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-08-27 13:32:02	2026-08-27 13:32:02	\N	t
\.


--
-- TOC entry 5601 (class 0 OID 28807)
-- Dependencies: 262
-- Data for Name: user_workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_workspaces (id, user_id, workspace_id, roles_id, join_date, status_active, updated_at, created_at) FROM stdin;
d53361ac-f1dd-45b8-99ff-87e525892bf2	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	3e26510b-3f68-417e-bcb1-61cae095bec0	a688ef38-3030-45cb-9a4d-0407605bc322	2026-07-10 23:26:46	t	2026-07-10 23:26:46	2026-07-10 23:26:46
00c874ef-510a-4df5-9c18-d703b39c5ac0	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	04d422ef-974e-4f86-a430-d0bfec6f29a3	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-07-14 22:24:46	t	2026-07-14 22:24:46	2026-07-14 22:24:46
b5706d3c-0dbd-4044-a6e6-8d3e76375394	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	5f1cb50a-2512-48f4-a184-e06a4665ef49	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-07-16 18:10:16	t	2026-07-16 18:10:16	2026-07-16 18:10:16
\.


--
-- TOC entry 5602 (class 0 OID 28816)
-- Dependencies: 263
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, full_name, email, password, google_id, status_active, created_at, updated_at, deleted_at, avatar, email_verified_at, onboarding_step, has_seen_onboarding, onboarding_type, system_role_id) FROM stdin;
e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	Admin Sistem Koladi	admin@koladi.com	$2y$12$4OIci5NuymVL4nmDyD8TPuIs5lWGR9suLsgPmf1BI.7.XG//Q1N4u	\N	t	2026-06-21 20:35:42	2026-06-28 21:31:39	\N	\N	2026-06-21 20:35:42	\N	t	full	33333333-3333-3333-3333-333333333333
019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	Rendi	kuliahbisa2005@gmail.com	$2y$12$w6KMSRRE4CnSBZuNLOPOuON3Hcd8bmyy.vv.vwvNxXEcwlssMEd12	117185357584893816951	t	2026-06-21 20:42:36	2026-07-14 22:24:05	\N	\N	\N	\N	f	member	\N
019f5015-b2cc-7144-82fa-fac0594f4e9b	Rendi Sinaga	rendysinagaa10@gmail.com	$2y$12$fvOdug0Zbmfx6fpqvUxra.zgqPylxSEVTgeSVOFuI.MuyEUhUgq4m	111369398795393028913	t	2026-07-11 14:30:27	2026-07-16 17:44:41	\N	\N	\N	\N	t	full	\N
019f6a8f-c28f-7090-97d5-113e5d166b06	Foto	rendyfoto10@gmail.com	$2y$12$Tc/w05Pa9QLtzVJxr2YSxuW0iSA..KXq2/j4w1OZCrliuUEzsDpZm	\N	t	2026-07-16 17:53:54	2026-07-16 17:54:09	\N	\N	\N	\N	t	member	\N
\.


--
-- TOC entry 5603 (class 0 OID 28830)
-- Dependencies: 264
-- Data for Name: workspace_performance_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspace_performance_snapshots (id, workspace_id, period_start, period_end, period_type, metrics, performance_score, quality_score, risk_score, suggestions, created_at, updated_at, version) FROM stdin;
019eead5-dd9f-737d-9173-6ebedc390108	3505e3a5-936a-4177-94bf-e8b59bbb7e20	2026-06-15	2026-06-21	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 100, "maxDelay": 0, "riskScore": 100, "onTimeRate": 0, "totalTasks": 1, "avgProgress": 0, "medianDelay": 0, "memberCount": 0, "overdueRate": 100, "maxLoadRatio": 0, "overdueCount": 1, "qualityScore": 0, "taskVelocity": 0, "avgDelayCapped": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "new", "urgentTaskRatio": 100, "performanceScore": 40, "avgTimeToDeadline": -356.2, "criticalTaskRatio": 100, "deadlineAdherence": 0, "hasCompletedTasks": false, "lateCompletionRate": 0}	40	0	100	{"actions": ["Review tugas yang terlambat dan cari solusi", "Prioritaskan tugas dengan deadline terdekat", "Evaluasi dan mitigasi risiko workspace", "Assign tugas ke anggota tim", "Mulai 2-3 tugas prioritas tinggi"], "warning": [{"title": "Banyak tugas belum dimulai", "value": "100%", "metric": "idleRate", "description": "1 dari 1 tugas belum dikerjakan", "suggestions": ["Assign tugas ke anggota tim", "Mulai 2-3 tugas prioritas tinggi", "Set target harian untuk tim"]}, {"title": "Ada tugas melewati deadline", "value": "100%", "metric": "overdueRate", "description": "1 tugas sudah lewat deadline tapi belum selesai", "suggestions": ["Prioritaskan tugas yang sudah overdue", "Review apakah deadline realistis", "Cek hambatan yang menghambat tim"]}], "critical": [{"title": "Banyak tugas terlambat dan sedikit yang selesai", "value": "100%", "metric": "overdueRate", "actions": ["Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan utama yang memperlambat tim", "Reschedule deadline jika memang tidak realistis"], "priority": 1, "description": "1 tugas melewati deadline, hanya 0% yang selesai"}, {"title": "Banyak deadline mendesak", "value": "100%", "metric": "urgentTaskRatio", "actions": ["URGENT: Review semua tugas overdue, prioritaskan yang paling kritis", "Identifikasi hambatan yang membuat tugas terlambat", "Pertimbangkan reschedule jika deadline tidak realistis"], "priority": 1, "description": "1 tugas sudah terlambat — Tim kesulitan mengejar deadline"}, {"title": "Tingkat risiko tinggi", "value": "100/100", "metric": "riskScore", "priority": 1, "description": "Workspace berisiko gagal mencapai target (skor risiko: 100/100)"}], "positive": [], "empty_state": false}	2026-06-21 22:39:04	2026-06-21 22:39:04	2.0
019f6a9d-c2e6-731d-ae27-663f4bc05965	5f1cb50a-2512-48f4-a184-e06a4665ef49	2026-07-13	2026-07-19	week	{"gini": 0, "wipRate": 25, "avgDelay": 0, "idleRate": 50, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 4, "avgProgress": 0, "medianDelay": 0, "memberCount": 0, "overdueRate": 0, "maxLoadRatio": 0, "overdueCount": 0, "qualityScore": 20, "taskVelocity": 0, "avgDelayCapped": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "active", "urgentTaskRatio": 0, "performanceScore": 4, "avgTimeToDeadline": 10.9, "criticalTaskRatio": 0, "deadlineAdherence": 0, "hasCompletedTasks": false, "lateCompletionRate": 0}	4	20	0	{"actions": ["Assign tugas ke anggota", "Mulai tugas prioritas tinggi"], "warning": [{"title": "Banyak tugas belum dimulai", "value": "50%", "metric": "idleRate", "description": "2 tugas masih menunggu dikerjakan", "suggestions": ["Assign tugas ke anggota", "Mulai tugas prioritas tinggi", "Cek apakah ada hambatan untuk mulai"]}], "critical": [], "positive": [], "empty_state": false}	2026-07-16 18:09:11	2026-07-16 18:09:11	2.0
019f6aa2-83da-711b-8f2c-6c0f650476de	1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	2026-07-13	2026-07-19	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-07-16 18:14:23	2026-07-16 18:14:23	2.0
\.


--
-- TOC entry 5604 (class 0 OID 28854)
-- Dependencies: 265
-- Data for Name: workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspaces (id, company_id, type, name, created_by, created_at, updated_at, deleted_at, description) FROM stdin;
3505e3a5-936a-4177-94bf-e8b59bbb7e20	fc21fabc-e558-4d71-b68e-616e6bdadb73	Tim	Front End Aplikasi	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-21 21:07:32	2026-06-21 21:07:32	\N	Membuat antarmuka aplikasi
bd3c87ed-6fc9-4f7d-9057-d53ccaf3c06e	5c8f25d9-540f-4673-a788-424129bde7df	Tim	Marketing	e9937b63-3aa7-4376-ae3d-e4bdbe5d0b4d	2026-06-28 21:32:00	2026-06-28 21:32:00	\N	Membuat Video FYP
a661a65f-3c55-4988-9a1a-e00b82045e7c	c6eba6e5-5e74-4523-9063-40cae1888779	Tim	Programming	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-06-30 15:11:26	2026-06-30 15:11:26	\N	Membuat aplikasi
3e26510b-3f68-417e-bcb1-61cae095bec0	c785040c-a4cc-4be8-a752-8a5ee215dc72	Proyek	PIDI Innovation Hub Proposal and Video Submission	019eea6b-3a5d-73f0-a4b5-ad75bc4fcd19	2026-07-10 23:26:46	2026-07-10 23:26:46	\N	Prepare and submit a validated innovation proposal and a 3-minute pitch video to qualify for the Top 80 final.\n\n**AI Clarification Questions to Client:**\n1. What is the exact deadline date (YYYY-MM-DD) for the proposal and video submission?\n2. Are there specific categories or themes that the innovation proposal must align with?\n3. What are the official links and forms required for the final submission?\n
00af975c-ad9b-4a10-bcf5-21fc788eef5c	05ff871f-a9e4-4603-bcbc-91ce388566d8	Proyek	TRPL	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:34:27	2026-07-16 17:59:07	2026-07-16 17:59:07	\N
04d422ef-974e-4f86-a430-d0bfec6f29a3	05ff871f-a9e4-4603-bcbc-91ce388566d8	Proyek	koladi	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-11 14:30:52	2026-07-16 17:59:13	2026-07-16 17:59:13	asda
5f1cb50a-2512-48f4-a184-e06a4665ef49	05ff871f-a9e4-4603-bcbc-91ce388566d8	Tim	Marketing	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 17:59:33	2026-07-16 17:59:33	\N	Membuat Konten
62cf7362-d1ac-4e13-9dee-7694082ef7b9	05ff871f-a9e4-4603-bcbc-91ce388566d8	Tim	Information	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:09:40	2026-07-16 18:09:40	\N	Membuat aplikasi
1e3cd7ba-317d-4c8d-b0a6-47b25a7ac16d	05ff871f-a9e4-4603-bcbc-91ce388566d8	Proyek	Membuat Aplikasi	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-07-16 18:10:04	2026-07-16 18:10:04	\N	Administrasi website
878834d3-87ad-459f-91e7-95536d600c4c	8253bba3-e703-4301-abee-a0b744e4fa81	Proyek	Ai	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-19 11:45:18	2026-08-19 11:45:18	\N	asfajsd
260cc0d8-81b0-453d-91b8-67d9ca92878a	2107bec9-4d93-4fd2-bf4f-65a2bcafa5f1	Proyek	ai	019f5015-b2cc-7144-82fa-fac0594f4e9b	2026-08-26 19:51:40	2026-08-26 19:51:40	\N	aiai
\.


--
-- TOC entry 5619 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedbacks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.feedbacks_id_seq', 1, false);


--
-- TOC entry 5620 (class 0 OID 0)
-- Dependencies: 247
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 8, true);


--
-- TOC entry 5621 (class 0 OID 0)
-- Dependencies: 252
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 3, true);


--
-- TOC entry 5197 (class 2606 OID 28869)
-- Name: addons addons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.addons
    ADD CONSTRAINT addons_pkey PRIMARY KEY (id);


--
-- TOC entry 5329 (class 2606 OID 48609)
-- Name: ai_processing_logs ai_processing_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_processing_logs
    ADD CONSTRAINT ai_processing_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 5199 (class 2606 OID 28871)
-- Name: announcement_recipients announcement_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5201 (class 2606 OID 28873)
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- TOC entry 5203 (class 2606 OID 28875)
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- TOC entry 5207 (class 2606 OID 28877)
-- Name: board_columns board_columns_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_pkey PRIMARY KEY (id);


--
-- TOC entry 5212 (class 2606 OID 28879)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 5210 (class 2606 OID 28881)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 5214 (class 2606 OID 28883)
-- Name: calendar_events calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


--
-- TOC entry 5216 (class 2606 OID 28885)
-- Name: calendar_participants calendar_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 5218 (class 2606 OID 28887)
-- Name: checklists checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_pkey PRIMARY KEY (id);


--
-- TOC entry 5221 (class 2606 OID 28889)
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- TOC entry 5223 (class 2606 OID 28891)
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- TOC entry 5225 (class 2606 OID 28893)
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- TOC entry 5227 (class 2606 OID 28895)
-- Name: conversation_participants conversation_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 5231 (class 2606 OID 28897)
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- TOC entry 5324 (class 2606 OID 37536)
-- Name: decisions decisions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_pkey PRIMARY KEY (id);


--
-- TOC entry 5236 (class 2606 OID 28899)
-- Name: document_recipients document_recipients_document_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_document_id_user_id_unique UNIQUE (document_id, user_id);


--
-- TOC entry 5238 (class 2606 OID 28901)
-- Name: document_recipients document_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5240 (class 2606 OID 28903)
-- Name: feedbacks feedbacks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks
    ADD CONSTRAINT feedbacks_pkey PRIMARY KEY (id);


--
-- TOC entry 5242 (class 2606 OID 28905)
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- TOC entry 5245 (class 2606 OID 28907)
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- TOC entry 5248 (class 2606 OID 28909)
-- Name: insight_recipients insight_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5250 (class 2606 OID 28911)
-- Name: insights insights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_pkey PRIMARY KEY (id);


--
-- TOC entry 5252 (class 2606 OID 28913)
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- TOC entry 5254 (class 2606 OID 28915)
-- Name: labels labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (id);


--
-- TOC entry 5256 (class 2606 OID 28917)
-- Name: leave_requests leave_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_pkey PRIMARY KEY (id);


--
-- TOC entry 5259 (class 2606 OID 28919)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 5261 (class 2606 OID 28921)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5265 (class 2606 OID 28923)
-- Name: mindmap_nodes mindmap_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_pkey PRIMARY KEY (id);


--
-- TOC entry 5267 (class 2606 OID 28925)
-- Name: mindmaps mindmaps_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_pkey PRIMARY KEY (id);


--
-- TOC entry 5270 (class 2606 OID 28927)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5275 (class 2606 OID 28929)
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5277 (class 2606 OID 28931)
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- TOC entry 5279 (class 2606 OID 28933)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 5281 (class 2606 OID 28935)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5286 (class 2606 OID 28937)
-- Name: subscription_invoices subscription_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_pkey PRIMARY KEY (id);


--
-- TOC entry 5291 (class 2606 OID 28939)
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- TOC entry 5322 (class 2606 OID 37520)
-- Name: task_activities task_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_activities
    ADD CONSTRAINT task_activities_pkey PRIMARY KEY (id);


--
-- TOC entry 5296 (class 2606 OID 28941)
-- Name: task_assignments task_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 5300 (class 2606 OID 28943)
-- Name: task_labels task_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_pkey PRIMARY KEY (task_id, label_id);


--
-- TOC entry 5307 (class 2606 OID 28945)
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- TOC entry 5309 (class 2606 OID 28947)
-- Name: user_companies user_companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_pkey PRIMARY KEY (id);


--
-- TOC entry 5311 (class 2606 OID 28949)
-- Name: user_workspaces user_workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 5313 (class 2606 OID 28951)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 5315 (class 2606 OID 28953)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5317 (class 2606 OID 28955)
-- Name: workspace_performance_snapshots workspace_performance_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT workspace_performance_snapshots_pkey PRIMARY KEY (id);


--
-- TOC entry 5320 (class 2606 OID 28957)
-- Name: workspaces workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 5232 (class 1259 OID 28958)
-- Name: conversations_scope_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_company_id_index ON public.conversations USING btree (scope, company_id);


--
-- TOC entry 5233 (class 1259 OID 28959)
-- Name: conversations_scope_workspace_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_workspace_id_index ON public.conversations USING btree (scope, workspace_id);


--
-- TOC entry 5330 (class 1259 OID 48611)
-- Name: idx_ai_processing_logs_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ai_processing_logs_user_id ON public.ai_processing_logs USING btree (user_id);


--
-- TOC entry 5331 (class 1259 OID 48610)
-- Name: idx_ai_processing_logs_workspace_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ai_processing_logs_workspace_id ON public.ai_processing_logs USING btree (workspace_id);


--
-- TOC entry 5204 (class 1259 OID 28960)
-- Name: idx_attachments_attachable; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_attachable ON public.attachments USING btree (attachable_type, attachable_id);


--
-- TOC entry 5205 (class 1259 OID 28961)
-- Name: idx_attachments_uploaded_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_uploaded_by ON public.attachments USING btree (uploaded_by);


--
-- TOC entry 5208 (class 1259 OID 28962)
-- Name: idx_board_columns_workspace_position; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_board_columns_workspace_position ON public.board_columns USING btree (workspace_id, "position");


--
-- TOC entry 5219 (class 1259 OID 28963)
-- Name: idx_checklists_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_checklists_task ON public.checklists USING btree (task_id);


--
-- TOC entry 5228 (class 1259 OID 28964)
-- Name: idx_conversation_participants_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_conversation_id ON public.conversation_participants USING btree (conversation_id);


--
-- TOC entry 5229 (class 1259 OID 28965)
-- Name: idx_conversation_participants_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_user_id ON public.conversation_participants USING btree (user_id);


--
-- TOC entry 5234 (class 1259 OID 28966)
-- Name: idx_conversations_last_message_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversations_last_message_id ON public.conversations USING btree (last_message_id);


--
-- TOC entry 5325 (class 1259 OID 37559)
-- Name: idx_decisions_decision_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_decisions_decision_date ON public.decisions USING btree (decision_date);


--
-- TOC entry 5326 (class 1259 OID 37558)
-- Name: idx_decisions_evidence_file_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_decisions_evidence_file_id ON public.decisions USING btree (evidence_file_id);


--
-- TOC entry 5327 (class 1259 OID 37557)
-- Name: idx_decisions_workspace_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_decisions_workspace_id ON public.decisions USING btree (workspace_id);


--
-- TOC entry 5243 (class 1259 OID 28967)
-- Name: idx_files_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_files_company_id ON public.files USING btree (company_id);


--
-- TOC entry 5246 (class 1259 OID 28968)
-- Name: idx_folders_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_folders_company_id ON public.folders USING btree (company_id);


--
-- TOC entry 5257 (class 1259 OID 28969)
-- Name: idx_messages_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_messages_conversation_id ON public.messages USING btree (conversation_id);


--
-- TOC entry 5262 (class 1259 OID 28970)
-- Name: idx_mindmap_nodes_mindmap_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_mindmap_id ON public.mindmap_nodes USING btree (mindmap_id);


--
-- TOC entry 5263 (class 1259 OID 28971)
-- Name: idx_mindmap_nodes_parent_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_parent_id ON public.mindmap_nodes USING btree (parent_id);


--
-- TOC entry 5282 (class 1259 OID 28972)
-- Name: idx_subscription_invoices_payment_method; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_payment_method ON public.subscription_invoices USING btree (payment_method);


--
-- TOC entry 5283 (class 1259 OID 28973)
-- Name: idx_subscription_invoices_verified_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_verified_at ON public.subscription_invoices USING btree (verified_at);


--
-- TOC entry 5293 (class 1259 OID 28974)
-- Name: idx_task_assignments_task_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_task_user ON public.task_assignments USING btree (task_id, user_id);


--
-- TOC entry 5294 (class 1259 OID 28975)
-- Name: idx_task_assignments_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_user ON public.task_assignments USING btree (user_id);


--
-- TOC entry 5297 (class 1259 OID 28976)
-- Name: idx_task_labels_label; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_label ON public.task_labels USING btree (label_id);


--
-- TOC entry 5298 (class 1259 OID 28977)
-- Name: idx_task_labels_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_task ON public.task_labels USING btree (task_id);


--
-- TOC entry 5301 (class 1259 OID 28978)
-- Name: idx_tasks_due_datetime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_due_datetime ON public.tasks USING btree (due_datetime) WHERE (deleted_at IS NULL);


--
-- TOC entry 5302 (class 1259 OID 28979)
-- Name: idx_tasks_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_status ON public.tasks USING btree (status) WHERE (deleted_at IS NULL);


--
-- TOC entry 5303 (class 1259 OID 28980)
-- Name: idx_tasks_workspace_column; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_column ON public.tasks USING btree (workspace_id, board_column_id);


--
-- TOC entry 5304 (class 1259 OID 28981)
-- Name: idx_tasks_workspace_creator; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_creator ON public.tasks USING btree (workspace_id, created_by);


--
-- TOC entry 5305 (class 1259 OID 28982)
-- Name: idx_tasks_workspace_secret; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_secret ON public.tasks USING btree (workspace_id, is_secret);


--
-- TOC entry 5268 (class 1259 OID 28983)
-- Name: notifications_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_created_at_index ON public.notifications USING btree (created_at);


--
-- TOC entry 5271 (class 1259 OID 28984)
-- Name: notifications_type_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_type_user_id_index ON public.notifications USING btree (type, user_id);


--
-- TOC entry 5272 (class 1259 OID 28985)
-- Name: notifications_user_id_company_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_user_id_company_id_is_read_index ON public.notifications USING btree (user_id, company_id, is_read);


--
-- TOC entry 5273 (class 1259 OID 28986)
-- Name: otp_verifications_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_email_index ON public.otp_verifications USING btree (email);


--
-- TOC entry 5284 (class 1259 OID 28987)
-- Name: subscription_invoices_external_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_external_id_index ON public.subscription_invoices USING btree (external_id);


--
-- TOC entry 5287 (class 1259 OID 28988)
-- Name: subscription_invoices_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_status_index ON public.subscription_invoices USING btree (status);


--
-- TOC entry 5288 (class 1259 OID 28989)
-- Name: subscription_invoices_subscription_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_subscription_id_index ON public.subscription_invoices USING btree (subscription_id);


--
-- TOC entry 5289 (class 1259 OID 28990)
-- Name: subscriptions_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_company_id_index ON public.subscriptions USING btree (company_id);


--
-- TOC entry 5292 (class 1259 OID 28991)
-- Name: subscriptions_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_status_index ON public.subscriptions USING btree (status);


--
-- TOC entry 5318 (class 1259 OID 28992)
-- Name: ws_perf_idx_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ws_perf_idx_created_at ON public.workspace_performance_snapshots USING btree (created_at);


--
-- TOC entry 5411 (class 2620 OID 28993)
-- Name: invitations update_invitations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_invitations_updated_at BEFORE UPDATE ON public.invitations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 5409 (class 2606 OID 48598)
-- Name: ai_processing_logs ai_processing_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_processing_logs
    ADD CONSTRAINT ai_processing_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5410 (class 2606 OID 48603)
-- Name: ai_processing_logs ai_processing_logs_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_processing_logs
    ADD CONSTRAINT ai_processing_logs_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5332 (class 2606 OID 28994)
-- Name: announcement_recipients announcement_recipients_announcement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_announcement_id_fkey FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- TOC entry 5333 (class 2606 OID 28999)
-- Name: announcement_recipients announcement_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5334 (class 2606 OID 29004)
-- Name: announcements announcements_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- TOC entry 5335 (class 2606 OID 29009)
-- Name: announcements announcements_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5336 (class 2606 OID 29014)
-- Name: announcements announcements_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5337 (class 2606 OID 29019)
-- Name: attachments attachments_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 5338 (class 2606 OID 29024)
-- Name: board_columns board_columns_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5339 (class 2606 OID 29029)
-- Name: board_columns board_columns_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5340 (class 2606 OID 29034)
-- Name: calendar_events calendar_events_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5341 (class 2606 OID 29039)
-- Name: calendar_events calendar_events_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5343 (class 2606 OID 29044)
-- Name: calendar_participants calendar_participants_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.calendar_events(id) ON DELETE CASCADE;


--
-- TOC entry 5344 (class 2606 OID 29049)
-- Name: calendar_participants calendar_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5345 (class 2606 OID 29054)
-- Name: checklists checklists_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5346 (class 2606 OID 29059)
-- Name: comments comments_parent_comment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_parent_comment_id_fkey FOREIGN KEY (parent_comment_id) REFERENCES public.comments(id) ON DELETE CASCADE;


--
-- TOC entry 5347 (class 2606 OID 29064)
-- Name: comments comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 5348 (class 2606 OID 29069)
-- Name: conversation_participants conversation_participants_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5349 (class 2606 OID 29074)
-- Name: conversation_participants conversation_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5350 (class 2606 OID 29079)
-- Name: conversations conversations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5351 (class 2606 OID 29084)
-- Name: conversations conversations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5352 (class 2606 OID 29089)
-- Name: conversations conversations_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5353 (class 2606 OID 29094)
-- Name: conversations conversations_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id);


--
-- TOC entry 5405 (class 2606 OID 37542)
-- Name: decisions decisions_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5406 (class 2606 OID 37547)
-- Name: decisions decisions_evidence_file_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_evidence_file_id_fkey FOREIGN KEY (evidence_file_id) REFERENCES public.files(id) ON DELETE SET NULL;


--
-- TOC entry 5407 (class 2606 OID 37552)
-- Name: decisions decisions_validated_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_validated_by_fkey FOREIGN KEY (validated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5408 (class 2606 OID 37537)
-- Name: decisions decisions_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.decisions
    ADD CONSTRAINT decisions_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5355 (class 2606 OID 29099)
-- Name: files files_folder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- TOC entry 5356 (class 2606 OID 29104)
-- Name: files files_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 5357 (class 2606 OID 29109)
-- Name: files files_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5342 (class 2606 OID 29114)
-- Name: calendar_events fk_calendar_events_company_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT fk_calendar_events_company_id FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5354 (class 2606 OID 29119)
-- Name: conversations fk_conversations_last_message; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT fk_conversations_last_message FOREIGN KEY (last_message_id) REFERENCES public.messages(id) ON DELETE SET NULL;


--
-- TOC entry 5358 (class 2606 OID 29124)
-- Name: files fk_files_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT fk_files_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5359 (class 2606 OID 29129)
-- Name: folders fk_folders_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_folders_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5384 (class 2606 OID 29134)
-- Name: subscription_invoices fk_subscription_invoices_verified_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT fk_subscription_invoices_verified_by FOREIGN KEY (verified_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5402 (class 2606 OID 29139)
-- Name: workspace_performance_snapshots fk_wps_workspace; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT fk_wps_workspace FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5360 (class 2606 OID 29144)
-- Name: folders folders_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5361 (class 2606 OID 29149)
-- Name: folders folders_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5362 (class 2606 OID 29154)
-- Name: insight_recipients insight_recipients_insight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_insight_id_fkey FOREIGN KEY (insight_id) REFERENCES public.insights(id) ON DELETE CASCADE;


--
-- TOC entry 5363 (class 2606 OID 29159)
-- Name: insight_recipients insight_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5364 (class 2606 OID 29164)
-- Name: insights insights_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5365 (class 2606 OID 29169)
-- Name: insights insights_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5366 (class 2606 OID 29174)
-- Name: invitations invitations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id);


--
-- TOC entry 5367 (class 2606 OID 29179)
-- Name: invitations invitations_invited_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_invited_by_fkey FOREIGN KEY (invited_by) REFERENCES public.users(id);


--
-- TOC entry 5368 (class 2606 OID 29184)
-- Name: labels labels_color_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_color_id_fkey FOREIGN KEY (color_id) REFERENCES public.colors(id);


--
-- TOC entry 5369 (class 2606 OID 38053)
-- Name: labels labels_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5370 (class 2606 OID 29189)
-- Name: leave_requests leave_requests_approved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- TOC entry 5371 (class 2606 OID 29194)
-- Name: leave_requests leave_requests_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5372 (class 2606 OID 29199)
-- Name: leave_requests leave_requests_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5373 (class 2606 OID 29204)
-- Name: messages messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5374 (class 2606 OID 29209)
-- Name: messages messages_reply_to_message_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_reply_to_message_id_fkey FOREIGN KEY (reply_to_message_id) REFERENCES public.messages(id);


--
-- TOC entry 5375 (class 2606 OID 29214)
-- Name: messages messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id);


--
-- TOC entry 5376 (class 2606 OID 29219)
-- Name: mindmap_nodes mindmap_nodes_mindmap_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_mindmap_id_fkey FOREIGN KEY (mindmap_id) REFERENCES public.mindmaps(id) ON DELETE CASCADE;


--
-- TOC entry 5377 (class 2606 OID 29224)
-- Name: mindmap_nodes mindmap_nodes_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.mindmap_nodes(id) ON DELETE CASCADE;


--
-- TOC entry 5378 (class 2606 OID 29229)
-- Name: mindmaps mindmaps_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5379 (class 2606 OID 29234)
-- Name: notifications notifications_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5380 (class 2606 OID 29239)
-- Name: notifications notifications_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5381 (class 2606 OID 29244)
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5382 (class 2606 OID 29249)
-- Name: notifications notifications_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5383 (class 2606 OID 29254)
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5385 (class 2606 OID 29259)
-- Name: subscription_invoices subscription_invoices_subscription_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES public.subscriptions(id) ON DELETE CASCADE;


--
-- TOC entry 5386 (class 2606 OID 29264)
-- Name: subscriptions subscriptions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5387 (class 2606 OID 29269)
-- Name: subscriptions subscriptions_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- TOC entry 5388 (class 2606 OID 29274)
-- Name: task_assignments task_assignments_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5389 (class 2606 OID 29279)
-- Name: task_assignments task_assignments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5390 (class 2606 OID 29284)
-- Name: task_labels task_labels_label_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_label_id_foreign FOREIGN KEY (label_id) REFERENCES public.labels(id) ON DELETE CASCADE;


--
-- TOC entry 5391 (class 2606 OID 29289)
-- Name: task_labels task_labels_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5392 (class 2606 OID 29294)
-- Name: tasks tasks_board_column_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_board_column_id_fkey FOREIGN KEY (board_column_id) REFERENCES public.board_columns(id);


--
-- TOC entry 5393 (class 2606 OID 29299)
-- Name: tasks tasks_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5394 (class 2606 OID 29304)
-- Name: tasks tasks_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5395 (class 2606 OID 29309)
-- Name: user_companies user_companies_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5396 (class 2606 OID 29314)
-- Name: user_companies user_companies_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 5397 (class 2606 OID 29319)
-- Name: user_companies user_companies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5398 (class 2606 OID 29324)
-- Name: user_workspaces user_workspaces_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 5399 (class 2606 OID 29329)
-- Name: user_workspaces user_workspaces_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5400 (class 2606 OID 29334)
-- Name: user_workspaces user_workspaces_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5401 (class 2606 OID 29339)
-- Name: users users_system_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_system_role_id_foreign FOREIGN KEY (system_role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- TOC entry 5403 (class 2606 OID 29344)
-- Name: workspaces workspaces_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5404 (class 2606 OID 29349)
-- Name: workspaces workspaces_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


-- Completed on 2026-09-12 22:22:30

--
-- PostgreSQL database dump complete
--

\unrestrict taIhwPC8FXPYdDfe767Tiefqr6GXh4w2eFuCZRCFcQtGeowghRXH4fesv0MvjuE

