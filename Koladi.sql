--
-- PostgreSQL database dump
--

\restrict tNzsuVzQ2KJEIYgg0yIvaqUkn7ya9kIVgsMRN0i48odHPU0OVURdkT3RCbFMMF5

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

-- Started on 2025-12-28 13:17:44

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
-- TOC entry 5575 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- TOC entry 909 (class 1247 OID 28418)
-- Name: conversation_scope; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.conversation_scope AS ENUM (
    'workspace',
    'company'
);


ALTER TYPE public.conversation_scope OWNER TO postgres;

--
-- TOC entry 912 (class 1247 OID 28424)
-- Name: payment_method_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.payment_method_enum AS ENUM (
    'midtrans',
    'manual'
);


ALTER TYPE public.payment_method_enum OWNER TO postgres;

--
-- TOC entry 276 (class 1255 OID 28429)
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
-- TOC entry 5576 (class 0 OID 0)
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
    updated_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL
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
-- TOC entry 5577 (class 0 OID 0)
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
-- TOC entry 5578 (class 0 OID 0)
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
-- TOC entry 5579 (class 0 OID 0)
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
-- TOC entry 5580 (class 0 OID 0)
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
-- TOC entry 5087 (class 2604 OID 28864)
-- Name: feedbacks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks ALTER COLUMN id SET DEFAULT nextval('public.feedbacks_id_seq'::regclass);


--
-- TOC entry 5117 (class 2604 OID 28865)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 5131 (class 2604 OID 28866)
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- TOC entry 5524 (class 0 OID 28430)
-- Dependencies: 220
-- Data for Name: addons; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.addons (id, addon_name, price_per_user, description, is_active, created_at, updated_at) FROM stdin;
6e06fbfd-26f0-4fc4-898d-5e22c3e5833d	Tambahan User	4000.00	Tambah 1 user ke paket yang kamu pilih	t	2025-12-28 13:16:13	2025-12-28 13:16:13
\.


--
-- TOC entry 5525 (class 0 OID 28441)
-- Dependencies: 221
-- Data for Name: announcement_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcement_recipients (id, announcement_id, user_id) FROM stdin;
\.


--
-- TOC entry 5526 (class 0 OID 28446)
-- Dependencies: 222
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, workspace_id, created_by, title, description, due_date, is_private, created_at, updated_at, auto_due, company_id) FROM stdin;
\.


--
-- TOC entry 5527 (class 0 OID 28457)
-- Dependencies: 223
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachments (id, attachable_type, attachable_id, file_url, uploaded_by, uploaded_at, file_name, file_size, file_type, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5528 (class 0 OID 28468)
-- Dependencies: 224
-- Data for Name: board_columns; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.board_columns (id, workspace_id, name, "position", created_by, created_at, updated_at, deleted_at) FROM stdin;
960b37f9-4854-4e84-9de6-2802b6b13113	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	To Do List	1	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
9ad43e5c-f152-471d-afca-01b7ac94a3a3	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Dikerjakan	2	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
d28a0b6f-f2e0-4f21-af96-76a5ad667882	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Selesai	3	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
40b43736-3734-4835-a82f-de56f223fd73	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Batal	4	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
\.


--
-- TOC entry 5529 (class 0 OID 28476)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- TOC entry 5530 (class 0 OID 28484)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5531 (class 0 OID 28492)
-- Dependencies: 227
-- Data for Name: calendar_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_events (id, workspace_id, created_by, title, description, start_datetime, end_datetime, recurrence, is_private, is_online_meeting, meeting_link, created_at, updated_at, deleted_at, company_id, location) FROM stdin;
\.


--
-- TOC entry 5532 (class 0 OID 28504)
-- Dependencies: 228
-- Data for Name: calendar_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_participants (id, event_id, user_id, status, attendance) FROM stdin;
\.


--
-- TOC entry 5533 (class 0 OID 28510)
-- Dependencies: 229
-- Data for Name: checklists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.checklists (id, task_id, title, is_done, created_at, updated_at, "position") FROM stdin;
\.


--
-- TOC entry 5534 (class 0 OID 28521)
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
-- TOC entry 5535 (class 0 OID 28527)
-- Dependencies: 231
-- Data for Name: comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comments (id, parent_comment_id, commentable_type, commentable_id, user_id, content, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5536 (class 0 OID 28537)
-- Dependencies: 232
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, email, address, phone, created_at, updated_at, deleted_at, trial_start, trial_end, status) FROM stdin;
a55a03f3-2191-4b53-833c-d7de8ce62c9b	Kuliah	\N	\N	\N	2025-12-28 13:16:28	2025-12-28 13:16:28	\N	2025-12-28 13:16:28	2026-01-04 13:16:28	trial
\.


--
-- TOC entry 5537 (class 0 OID 28550)
-- Dependencies: 233
-- Data for Name: conversation_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversation_participants (id, conversation_id, user_id, joined_at, is_admin, last_read_at) FROM stdin;
\.


--
-- TOC entry 5538 (class 0 OID 28557)
-- Dependencies: 234
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, workspace_id, created_at, type, name, created_by, updated_at, last_message_id, scope, company_id) FROM stdin;
\.


--
-- TOC entry 5539 (class 0 OID 28566)
-- Dependencies: 235
-- Data for Name: document_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.document_recipients (id, document_id, user_id, status, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5540 (class 0 OID 28575)
-- Dependencies: 236
-- Data for Name: feedbacks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.feedbacks (id, name, email, message, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5542 (class 0 OID 28585)
-- Dependencies: 238
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.files (id, folder_id, workspace_id, file_url, is_private, uploaded_by, uploaded_at, file_name, file_path, file_size, file_type, company_id) FROM stdin;
\.


--
-- TOC entry 5543 (class 0 OID 28595)
-- Dependencies: 239
-- Data for Name: folders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.folders (id, workspace_id, name, is_private, created_by, created_at, updated_at, deleted_at, parent_id, company_id) FROM stdin;
\.


--
-- TOC entry 5544 (class 0 OID 28604)
-- Dependencies: 240
-- Data for Name: insight_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insight_recipients (id, insight_id, user_id) FROM stdin;
\.


--
-- TOC entry 5545 (class 0 OID 28609)
-- Dependencies: 241
-- Data for Name: insights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insights (id, workspace_id, created_by, description, delivery_days, delivery_time, is_private, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5546 (class 0 OID 28619)
-- Dependencies: 242
-- Data for Name: invitations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invitations (id, email_target, token, status, invited_by, company_id, created_at, expired_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5547 (class 0 OID 28631)
-- Dependencies: 243
-- Data for Name: labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.labels (id, name, color_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5548 (class 0 OID 28641)
-- Dependencies: 244
-- Data for Name: leave_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leave_requests (id, user_id, workspace_id, leave_type, start_date, end_date, reason, status, approved_by, attachment_url, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5549 (class 0 OID 28651)
-- Dependencies: 245
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, sender_id, content, message_type, reply_to_message_id, is_edited, edited_at, deleted_at, created_at, is_read, read_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5550 (class 0 OID 28661)
-- Dependencies: 246
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
3	2025_11_27_204411_create_subscription_tables	1
4	2025_11_30_205745_add_system_role_id_to_users_table	2
5	2025_12_20_180641_create_notifications_table	3
6	2025_12_23_110225_add_status_active_to_user_companies_table	4
\.


--
-- TOC entry 5552 (class 0 OID 28668)
-- Dependencies: 248
-- Data for Name: mindmap_nodes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmap_nodes (id, mindmap_id, parent_id, title, description, type, x_position, y_position, connection_side, sort_order, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5553 (class 0 OID 28684)
-- Dependencies: 249
-- Data for Name: mindmaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmaps (id, workspace_id, title, description, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5554 (class 0 OID 28696)
-- Dependencies: 250
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, company_id, workspace_id, type, title, message, context, notifiable_type, notifiable_id, actor_id, is_read, read_at, action_url, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5555 (class 0 OID 28712)
-- Dependencies: 251
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, email, otp, type, expires_at, is_used, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5557 (class 0 OID 28724)
-- Dependencies: 253
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.plans (id, plan_name, price_monthly, base_user_limit, description, is_active, created_at, updated_at) FROM stdin;
e0a92975-a9b3-4542-9930-83d756e01c04	Paket Basic	15000.00	5	Cocok untuk tim kecil	t	2025-12-28 13:16:13	2025-12-28 13:16:13
a2af066c-3bcc-4f67-a005-4cae640ea8b1	Paket Standard	45000.00	20	Untuk tim yang berkembang	t	2025-12-28 13:16:13	2025-12-28 13:16:13
044b0518-b4e8-4cef-9892-1f86311ae0f3	Paket Business	100000.00	50	Untuk organisasi besar	t	2025-12-28 13:16:13	2025-12-28 13:16:13
\.


--
-- TOC entry 5558 (class 0 OID 28736)
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
-- TOC entry 5559 (class 0 OID 28742)
-- Dependencies: 255
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
tAenl5Pbtp0dMh6vJSNe6RCrIA4hgFOFymnLLnYr	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVXBqZkhGdzY5OXlqemt2M056YnJjc3d6TDJyRWhRYTVnanlUdlg0TSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9rZWxvbGEtd29ya3NwYWNlIjtzOjU6InJvdXRlIjtzOjE2OiJrZWxvbGEtd29ya3NwYWNlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO3M6MzY6ImMyYjU0MmViLTBmMmItNGMxOC05ZjRiLTVlZjMwYTVlM2M4NiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2J1YXQtcGVydXNhaGFhbiI7fXM6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtzOjM2OiJhNTVhMDNmMy0yMTkxLTRiNTMtODMzYy1kN2RlOGNlNjJjOWIiO30=	1766902617
mdmaBSmG6QHJ3A8gFvMvFzy4yBagZUzqFSFnTMBa	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0ZWakxzYWdETFFvUGFzN2c2YjFsTE8xdEJ2OElvdkRFZ0o3Qm1vcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJtYXN1ayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1766902599
uXajj6SbHp3V6GlNTWN06dFwges4d7XnvnRCbdbh	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSkNZNklXZG1WNGcya0duVkk3bjRaR1ZBNk5BNWVUT3dhRXpnTzlBQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJtYXN1ayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1766902600
\.


--
-- TOC entry 5560 (class 0 OID 28750)
-- Dependencies: 256
-- Data for Name: subscription_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscription_invoices (id, subscription_id, external_id, payment_url, amount, billing_month, status, paid_at, payment_details, created_at, updated_at, payment_method, proof_of_payment, admin_notes, verified_at, verified_by, payer_name, payer_bank, payer_account_number) FROM stdin;
\.


--
-- TOC entry 5561 (class 0 OID 28765)
-- Dependencies: 257
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscriptions (id, company_id, plan_id, addons_user_count, total_user_limit, start_date, end_date, status, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- TOC entry 5562 (class 0 OID 28778)
-- Dependencies: 258
-- Data for Name: task_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_assignments (id, task_id, user_id, assigned_at) FROM stdin;
\.


--
-- TOC entry 5563 (class 0 OID 28784)
-- Dependencies: 259
-- Data for Name: task_labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_labels (task_id, label_id, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5564 (class 0 OID 28789)
-- Dependencies: 260
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tasks (id, workspace_id, created_by, title, description, status, board_column_id, priority, is_secret, start_datetime, due_datetime, created_at, updated_at, deleted_at, phase, completed_at) FROM stdin;
\.


--
-- TOC entry 5565 (class 0 OID 28800)
-- Dependencies: 261
-- Data for Name: user_companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_companies (id, user_id, company_id, roles_id, created_at, updated_at, deleted_at, status_active) FROM stdin;
64319d9f-611d-4329-8ebc-f6a512878b42	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	a55a03f3-2191-4b53-833c-d7de8ce62c9b	11111111-1111-1111-1111-111111111111	2025-12-28 13:16:28	2025-12-28 13:16:28	\N	t
\.


--
-- TOC entry 5566 (class 0 OID 28807)
-- Dependencies: 262
-- Data for Name: user_workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_workspaces (id, user_id, workspace_id, roles_id, join_date, status_active, updated_at, created_at) FROM stdin;
\.


--
-- TOC entry 5567 (class 0 OID 28816)
-- Dependencies: 263
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, full_name, email, password, google_id, status_active, created_at, updated_at, deleted_at, avatar, email_verified_at, onboarding_step, has_seen_onboarding, onboarding_type, system_role_id) FROM stdin;
08b53454-fd00-4bbb-88f7-aa64ba26fd55	Admin Sistem Koladi	admin@koladi.com	$2y$12$zKH6i.9qBxcmAtK2LitkoO94SLGss4xiOLCfN0eehDqQSYwmXaTQ2	\N	t	2025-12-28 13:16:13	2025-12-28 13:16:13	\N	\N	2025-12-28 13:16:13	\N	f	\N	33333333-3333-3333-3333-333333333333
c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	Kuliah	kuliahbisa2005@gmail.com	$2y$12$ViCEOMGDGYz7Za9k/or.r.cNg46Ubvgjz43UQVW1/qAW9Im5B/DRe	117185357584893816951	t	2025-12-28 13:16:19	2025-12-28 13:16:44	\N	\N	\N	workspace-created	f	full	\N
\.


--
-- TOC entry 5568 (class 0 OID 28830)
-- Dependencies: 264
-- Data for Name: workspace_performance_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspace_performance_snapshots (id, workspace_id, period_start, period_end, period_type, metrics, performance_score, quality_score, risk_score, suggestions, created_at, updated_at, version) FROM stdin;
\.


--
-- TOC entry 5569 (class 0 OID 28854)
-- Dependencies: 265
-- Data for Name: workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspaces (id, company_id, type, name, created_by, created_at, updated_at, deleted_at, description) FROM stdin;
f7b296c0-c0b4-496b-a55e-b99e3692e1cf	a55a03f3-2191-4b53-833c-d7de8ce62c9b	Tim	aaa	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N	aaa
\.


--
-- TOC entry 5581 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedbacks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.feedbacks_id_seq', 1, false);


--
-- TOC entry 5582 (class 0 OID 0)
-- Dependencies: 247
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 6, true);


--
-- TOC entry 5583 (class 0 OID 0)
-- Dependencies: 252
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 1, false);


--
-- TOC entry 5180 (class 2606 OID 28869)
-- Name: addons addons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.addons
    ADD CONSTRAINT addons_pkey PRIMARY KEY (id);


--
-- TOC entry 5182 (class 2606 OID 28871)
-- Name: announcement_recipients announcement_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5184 (class 2606 OID 28873)
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- TOC entry 5186 (class 2606 OID 28875)
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- TOC entry 5190 (class 2606 OID 28877)
-- Name: board_columns board_columns_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_pkey PRIMARY KEY (id);


--
-- TOC entry 5195 (class 2606 OID 28879)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 5193 (class 2606 OID 28881)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 5197 (class 2606 OID 28883)
-- Name: calendar_events calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


--
-- TOC entry 5199 (class 2606 OID 28885)
-- Name: calendar_participants calendar_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 5201 (class 2606 OID 28887)
-- Name: checklists checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_pkey PRIMARY KEY (id);


--
-- TOC entry 5204 (class 2606 OID 28889)
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- TOC entry 5206 (class 2606 OID 28891)
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- TOC entry 5208 (class 2606 OID 28893)
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- TOC entry 5210 (class 2606 OID 28895)
-- Name: conversation_participants conversation_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_pkey PRIMARY KEY (id);


--
-- TOC entry 5214 (class 2606 OID 28897)
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- TOC entry 5219 (class 2606 OID 28899)
-- Name: document_recipients document_recipients_document_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_document_id_user_id_unique UNIQUE (document_id, user_id);


--
-- TOC entry 5221 (class 2606 OID 28901)
-- Name: document_recipients document_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5223 (class 2606 OID 28903)
-- Name: feedbacks feedbacks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks
    ADD CONSTRAINT feedbacks_pkey PRIMARY KEY (id);


--
-- TOC entry 5225 (class 2606 OID 28905)
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- TOC entry 5228 (class 2606 OID 28907)
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- TOC entry 5231 (class 2606 OID 28909)
-- Name: insight_recipients insight_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_pkey PRIMARY KEY (id);


--
-- TOC entry 5233 (class 2606 OID 28911)
-- Name: insights insights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_pkey PRIMARY KEY (id);


--
-- TOC entry 5235 (class 2606 OID 28913)
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- TOC entry 5237 (class 2606 OID 28915)
-- Name: labels labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (id);


--
-- TOC entry 5239 (class 2606 OID 28917)
-- Name: leave_requests leave_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_pkey PRIMARY KEY (id);


--
-- TOC entry 5242 (class 2606 OID 28919)
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- TOC entry 5244 (class 2606 OID 28921)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5248 (class 2606 OID 28923)
-- Name: mindmap_nodes mindmap_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_pkey PRIMARY KEY (id);


--
-- TOC entry 5250 (class 2606 OID 28925)
-- Name: mindmaps mindmaps_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_pkey PRIMARY KEY (id);


--
-- TOC entry 5253 (class 2606 OID 28927)
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5258 (class 2606 OID 28929)
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- TOC entry 5260 (class 2606 OID 28931)
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- TOC entry 5262 (class 2606 OID 28933)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 5264 (class 2606 OID 28935)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5269 (class 2606 OID 28937)
-- Name: subscription_invoices subscription_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_pkey PRIMARY KEY (id);


--
-- TOC entry 5274 (class 2606 OID 28939)
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- TOC entry 5279 (class 2606 OID 28941)
-- Name: task_assignments task_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_pkey PRIMARY KEY (id);


--
-- TOC entry 5283 (class 2606 OID 28943)
-- Name: task_labels task_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_pkey PRIMARY KEY (task_id, label_id);


--
-- TOC entry 5290 (class 2606 OID 28945)
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- TOC entry 5292 (class 2606 OID 28947)
-- Name: user_companies user_companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_pkey PRIMARY KEY (id);


--
-- TOC entry 5294 (class 2606 OID 28949)
-- Name: user_workspaces user_workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 5296 (class 2606 OID 28951)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 5298 (class 2606 OID 28953)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5300 (class 2606 OID 28955)
-- Name: workspace_performance_snapshots workspace_performance_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT workspace_performance_snapshots_pkey PRIMARY KEY (id);


--
-- TOC entry 5303 (class 2606 OID 28957)
-- Name: workspaces workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_pkey PRIMARY KEY (id);


--
-- TOC entry 5215 (class 1259 OID 28958)
-- Name: conversations_scope_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_company_id_index ON public.conversations USING btree (scope, company_id);


--
-- TOC entry 5216 (class 1259 OID 28959)
-- Name: conversations_scope_workspace_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_workspace_id_index ON public.conversations USING btree (scope, workspace_id);


--
-- TOC entry 5187 (class 1259 OID 28960)
-- Name: idx_attachments_attachable; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_attachable ON public.attachments USING btree (attachable_type, attachable_id);


--
-- TOC entry 5188 (class 1259 OID 28961)
-- Name: idx_attachments_uploaded_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_uploaded_by ON public.attachments USING btree (uploaded_by);


--
-- TOC entry 5191 (class 1259 OID 28962)
-- Name: idx_board_columns_workspace_position; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_board_columns_workspace_position ON public.board_columns USING btree (workspace_id, "position");


--
-- TOC entry 5202 (class 1259 OID 28963)
-- Name: idx_checklists_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_checklists_task ON public.checklists USING btree (task_id);


--
-- TOC entry 5211 (class 1259 OID 28964)
-- Name: idx_conversation_participants_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_conversation_id ON public.conversation_participants USING btree (conversation_id);


--
-- TOC entry 5212 (class 1259 OID 28965)
-- Name: idx_conversation_participants_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_user_id ON public.conversation_participants USING btree (user_id);


--
-- TOC entry 5217 (class 1259 OID 28966)
-- Name: idx_conversations_last_message_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversations_last_message_id ON public.conversations USING btree (last_message_id);


--
-- TOC entry 5226 (class 1259 OID 28967)
-- Name: idx_files_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_files_company_id ON public.files USING btree (company_id);


--
-- TOC entry 5229 (class 1259 OID 28968)
-- Name: idx_folders_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_folders_company_id ON public.folders USING btree (company_id);


--
-- TOC entry 5240 (class 1259 OID 28969)
-- Name: idx_messages_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_messages_conversation_id ON public.messages USING btree (conversation_id);


--
-- TOC entry 5245 (class 1259 OID 28970)
-- Name: idx_mindmap_nodes_mindmap_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_mindmap_id ON public.mindmap_nodes USING btree (mindmap_id);


--
-- TOC entry 5246 (class 1259 OID 28971)
-- Name: idx_mindmap_nodes_parent_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_parent_id ON public.mindmap_nodes USING btree (parent_id);


--
-- TOC entry 5265 (class 1259 OID 28972)
-- Name: idx_subscription_invoices_payment_method; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_payment_method ON public.subscription_invoices USING btree (payment_method);


--
-- TOC entry 5266 (class 1259 OID 28973)
-- Name: idx_subscription_invoices_verified_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_verified_at ON public.subscription_invoices USING btree (verified_at);


--
-- TOC entry 5276 (class 1259 OID 28974)
-- Name: idx_task_assignments_task_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_task_user ON public.task_assignments USING btree (task_id, user_id);


--
-- TOC entry 5277 (class 1259 OID 28975)
-- Name: idx_task_assignments_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_user ON public.task_assignments USING btree (user_id);


--
-- TOC entry 5280 (class 1259 OID 28976)
-- Name: idx_task_labels_label; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_label ON public.task_labels USING btree (label_id);


--
-- TOC entry 5281 (class 1259 OID 28977)
-- Name: idx_task_labels_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_task ON public.task_labels USING btree (task_id);


--
-- TOC entry 5284 (class 1259 OID 28978)
-- Name: idx_tasks_due_datetime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_due_datetime ON public.tasks USING btree (due_datetime) WHERE (deleted_at IS NULL);


--
-- TOC entry 5285 (class 1259 OID 28979)
-- Name: idx_tasks_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_status ON public.tasks USING btree (status) WHERE (deleted_at IS NULL);


--
-- TOC entry 5286 (class 1259 OID 28980)
-- Name: idx_tasks_workspace_column; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_column ON public.tasks USING btree (workspace_id, board_column_id);


--
-- TOC entry 5287 (class 1259 OID 28981)
-- Name: idx_tasks_workspace_creator; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_creator ON public.tasks USING btree (workspace_id, created_by);


--
-- TOC entry 5288 (class 1259 OID 28982)
-- Name: idx_tasks_workspace_secret; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_secret ON public.tasks USING btree (workspace_id, is_secret);


--
-- TOC entry 5251 (class 1259 OID 28983)
-- Name: notifications_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_created_at_index ON public.notifications USING btree (created_at);


--
-- TOC entry 5254 (class 1259 OID 28984)
-- Name: notifications_type_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_type_user_id_index ON public.notifications USING btree (type, user_id);


--
-- TOC entry 5255 (class 1259 OID 28985)
-- Name: notifications_user_id_company_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_user_id_company_id_is_read_index ON public.notifications USING btree (user_id, company_id, is_read);


--
-- TOC entry 5256 (class 1259 OID 28986)
-- Name: otp_verifications_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_email_index ON public.otp_verifications USING btree (email);


--
-- TOC entry 5267 (class 1259 OID 28987)
-- Name: subscription_invoices_external_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_external_id_index ON public.subscription_invoices USING btree (external_id);


--
-- TOC entry 5270 (class 1259 OID 28988)
-- Name: subscription_invoices_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_status_index ON public.subscription_invoices USING btree (status);


--
-- TOC entry 5271 (class 1259 OID 28989)
-- Name: subscription_invoices_subscription_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_subscription_id_index ON public.subscription_invoices USING btree (subscription_id);


--
-- TOC entry 5272 (class 1259 OID 28990)
-- Name: subscriptions_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_company_id_index ON public.subscriptions USING btree (company_id);


--
-- TOC entry 5275 (class 1259 OID 28991)
-- Name: subscriptions_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_status_index ON public.subscriptions USING btree (status);


--
-- TOC entry 5301 (class 1259 OID 28992)
-- Name: ws_perf_idx_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ws_perf_idx_created_at ON public.workspace_performance_snapshots USING btree (created_at);


--
-- TOC entry 5376 (class 2620 OID 28993)
-- Name: invitations update_invitations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_invitations_updated_at BEFORE UPDATE ON public.invitations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 5304 (class 2606 OID 28994)
-- Name: announcement_recipients announcement_recipients_announcement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_announcement_id_fkey FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- TOC entry 5305 (class 2606 OID 28999)
-- Name: announcement_recipients announcement_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5306 (class 2606 OID 29004)
-- Name: announcements announcements_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- TOC entry 5307 (class 2606 OID 29009)
-- Name: announcements announcements_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5308 (class 2606 OID 29014)
-- Name: announcements announcements_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5309 (class 2606 OID 29019)
-- Name: attachments attachments_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 5310 (class 2606 OID 29024)
-- Name: board_columns board_columns_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5311 (class 2606 OID 29029)
-- Name: board_columns board_columns_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5312 (class 2606 OID 29034)
-- Name: calendar_events calendar_events_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5313 (class 2606 OID 29039)
-- Name: calendar_events calendar_events_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5315 (class 2606 OID 29044)
-- Name: calendar_participants calendar_participants_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.calendar_events(id) ON DELETE CASCADE;


--
-- TOC entry 5316 (class 2606 OID 29049)
-- Name: calendar_participants calendar_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5317 (class 2606 OID 29054)
-- Name: checklists checklists_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5318 (class 2606 OID 29059)
-- Name: comments comments_parent_comment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_parent_comment_id_fkey FOREIGN KEY (parent_comment_id) REFERENCES public.comments(id) ON DELETE CASCADE;


--
-- TOC entry 5319 (class 2606 OID 29064)
-- Name: comments comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 5320 (class 2606 OID 29069)
-- Name: conversation_participants conversation_participants_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5321 (class 2606 OID 29074)
-- Name: conversation_participants conversation_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5322 (class 2606 OID 29079)
-- Name: conversations conversations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5323 (class 2606 OID 29084)
-- Name: conversations conversations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5324 (class 2606 OID 29089)
-- Name: conversations conversations_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5325 (class 2606 OID 29094)
-- Name: conversations conversations_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id);


--
-- TOC entry 5327 (class 2606 OID 29099)
-- Name: files files_folder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- TOC entry 5328 (class 2606 OID 29104)
-- Name: files files_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- TOC entry 5329 (class 2606 OID 29109)
-- Name: files files_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5314 (class 2606 OID 29114)
-- Name: calendar_events fk_calendar_events_company_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT fk_calendar_events_company_id FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5326 (class 2606 OID 29119)
-- Name: conversations fk_conversations_last_message; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT fk_conversations_last_message FOREIGN KEY (last_message_id) REFERENCES public.messages(id) ON DELETE SET NULL;


--
-- TOC entry 5330 (class 2606 OID 29124)
-- Name: files fk_files_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT fk_files_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5331 (class 2606 OID 29129)
-- Name: folders fk_folders_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_folders_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5355 (class 2606 OID 29134)
-- Name: subscription_invoices fk_subscription_invoices_verified_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT fk_subscription_invoices_verified_by FOREIGN KEY (verified_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5373 (class 2606 OID 29139)
-- Name: workspace_performance_snapshots fk_wps_workspace; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT fk_wps_workspace FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5332 (class 2606 OID 29144)
-- Name: folders folders_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5333 (class 2606 OID 29149)
-- Name: folders folders_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5334 (class 2606 OID 29154)
-- Name: insight_recipients insight_recipients_insight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_insight_id_fkey FOREIGN KEY (insight_id) REFERENCES public.insights(id) ON DELETE CASCADE;


--
-- TOC entry 5335 (class 2606 OID 29159)
-- Name: insight_recipients insight_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5336 (class 2606 OID 29164)
-- Name: insights insights_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5337 (class 2606 OID 29169)
-- Name: insights insights_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5338 (class 2606 OID 29174)
-- Name: invitations invitations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id);


--
-- TOC entry 5339 (class 2606 OID 29179)
-- Name: invitations invitations_invited_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_invited_by_fkey FOREIGN KEY (invited_by) REFERENCES public.users(id);


--
-- TOC entry 5340 (class 2606 OID 29184)
-- Name: labels labels_color_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_color_id_fkey FOREIGN KEY (color_id) REFERENCES public.colors(id);


--
-- TOC entry 5341 (class 2606 OID 29189)
-- Name: leave_requests leave_requests_approved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- TOC entry 5342 (class 2606 OID 29194)
-- Name: leave_requests leave_requests_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5343 (class 2606 OID 29199)
-- Name: leave_requests leave_requests_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5344 (class 2606 OID 29204)
-- Name: messages messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- TOC entry 5345 (class 2606 OID 29209)
-- Name: messages messages_reply_to_message_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_reply_to_message_id_fkey FOREIGN KEY (reply_to_message_id) REFERENCES public.messages(id);


--
-- TOC entry 5346 (class 2606 OID 29214)
-- Name: messages messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id);


--
-- TOC entry 5347 (class 2606 OID 29219)
-- Name: mindmap_nodes mindmap_nodes_mindmap_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_mindmap_id_fkey FOREIGN KEY (mindmap_id) REFERENCES public.mindmaps(id) ON DELETE CASCADE;


--
-- TOC entry 5348 (class 2606 OID 29224)
-- Name: mindmap_nodes mindmap_nodes_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.mindmap_nodes(id) ON DELETE CASCADE;


--
-- TOC entry 5349 (class 2606 OID 29229)
-- Name: mindmaps mindmaps_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5350 (class 2606 OID 29234)
-- Name: notifications notifications_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5351 (class 2606 OID 29239)
-- Name: notifications notifications_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5352 (class 2606 OID 29244)
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5353 (class 2606 OID 29249)
-- Name: notifications notifications_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5354 (class 2606 OID 29254)
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5356 (class 2606 OID 29259)
-- Name: subscription_invoices subscription_invoices_subscription_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES public.subscriptions(id) ON DELETE CASCADE;


--
-- TOC entry 5357 (class 2606 OID 29264)
-- Name: subscriptions subscriptions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5358 (class 2606 OID 29269)
-- Name: subscriptions subscriptions_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- TOC entry 5359 (class 2606 OID 29274)
-- Name: task_assignments task_assignments_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5360 (class 2606 OID 29279)
-- Name: task_assignments task_assignments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5361 (class 2606 OID 29284)
-- Name: task_labels task_labels_label_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_label_id_foreign FOREIGN KEY (label_id) REFERENCES public.labels(id) ON DELETE CASCADE;


--
-- TOC entry 5362 (class 2606 OID 29289)
-- Name: task_labels task_labels_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- TOC entry 5363 (class 2606 OID 29294)
-- Name: tasks tasks_board_column_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_board_column_id_fkey FOREIGN KEY (board_column_id) REFERENCES public.board_columns(id);


--
-- TOC entry 5364 (class 2606 OID 29299)
-- Name: tasks tasks_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- TOC entry 5365 (class 2606 OID 29304)
-- Name: tasks tasks_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5366 (class 2606 OID 29309)
-- Name: user_companies user_companies_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5367 (class 2606 OID 29314)
-- Name: user_companies user_companies_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 5368 (class 2606 OID 29319)
-- Name: user_companies user_companies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5369 (class 2606 OID 29324)
-- Name: user_workspaces user_workspaces_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- TOC entry 5370 (class 2606 OID 29329)
-- Name: user_workspaces user_workspaces_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5371 (class 2606 OID 29334)
-- Name: user_workspaces user_workspaces_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- TOC entry 5372 (class 2606 OID 29339)
-- Name: users users_system_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_system_role_id_foreign FOREIGN KEY (system_role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- TOC entry 5374 (class 2606 OID 29344)
-- Name: workspaces workspaces_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- TOC entry 5375 (class 2606 OID 29349)
-- Name: workspaces workspaces_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


-- Completed on 2025-12-28 13:17:44

--
-- PostgreSQL database dump complete
--

\unrestrict tNzsuVzQ2KJEIYgg0yIvaqUkn7ya9kIVgsMRN0i48odHPU0OVURdkT3RCbFMMF5

